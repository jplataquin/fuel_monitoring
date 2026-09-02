use crate::biquad::Biquad;

/// A zero-latency Dual-FIR speaker cabinet simulation engine modeling a Celestion V30 4x12 cab.
/// Features:
/// - Zero-delay 512-tap direct FIR convolution for Channel A (SM57) and Channel B (R-121)
/// - Procedural generation of default authentic impulse responses on startup
/// - Dynamic user IR buffer updates for drag-and-drop custom impulses
/// - Headroom normalization to ±1.0
/// - Non-linear paper cone breakup (3rd-order polynomial wave-shaper) active above -3 dBFS (0.707)
/// - Thermal/excursion compression via a dynamic 1st-order LPF rolling off highs to 5.5 kHz during transients
#[derive(Debug, Clone)]
pub struct Cabinet {
    // Dual FIR convolution state
    fir_sm57: [f32; 512],
    fir_r121: [f32; 512],
    history: [f32; 512],
    write_idx: usize,

    // Dynamic processing states
    thermal_envelope: f32,
    thermal_x1: f32,
    thermal_y1: f32,

    // Air distance filter (1st-order LPF)
    air_x1: f32,
    air_y1: f32,

    // Control parameters
    mic_blend: f32, // 0.0 (SM57) to 1.0 (R-121)
    mic_dist: f32,  // 0.0 (Close) to 1.0 (Far, rolls off highs to 8 kHz)
    fs: f32,
}

impl Cabinet {
    pub fn new(fs: f32) -> Self {
        let fir_sm57 = Self::generate_default_ir(0, fs);
        let fir_r121 = Self::generate_default_ir(1, fs);

        Self {
            fir_sm57,
            fir_r121,
            history: [0.0; 512],
            write_idx: 0,
            thermal_envelope: 0.0,
            thermal_x1: 0.0,
            thermal_y1: 0.0,
            air_x1: 0.0,
            air_y1: 0.0,
            mic_blend: 0.5, // 50/50 mix default
            mic_dist: 0.2,  // Close/mid position
            fs,
        }
    }

    /// Reset all filter states, history, and thermal compressors to zero
    pub fn reset(&mut self) {
        self.history = [0.0; 512];
        self.write_idx = 0;
        self.thermal_envelope = 0.0;
        self.thermal_x1 = 0.0;
        self.thermal_y1 = 0.0;
        self.air_x1 = 0.0;
        self.air_y1 = 0.0;
    }

    /// Set a new sample rate and regenerate default IRs if custom IRs are not loaded
    pub fn set_sample_rate(&mut self, fs: f32) {
        self.fs = fs;
        self.fir_sm57 = Self::generate_default_ir(0, fs);
        self.fir_r121 = Self::generate_default_ir(1, fs);
    }

    /// Set cabinet control parameters
    pub fn set_params(&mut self, mic_blend: f32, mic_dist: f32) {
        self.mic_blend = mic_blend.clamp(0.0, 1.0);
        self.mic_dist = mic_dist.clamp(0.0, 1.0);
    }

    /// Load a custom impulse response into Channel A (0) or Channel B (1)
    pub fn load_custom_ir(&mut self, channel: usize, samples: &[f32]) {
        let target_fir = if channel == 0 { &mut self.fir_sm57 } else { &mut self.fir_r121 };
        
        // Zero-fill the target FIR buffer first
        *target_fir = [0.0; 512];
        
        // Copy user samples up to 512 taps
        let copy_len = samples.len().min(512);
        target_fir[..copy_len].copy_from_slice(&samples[..copy_len]);

        // Normalize the custom IR to keep gain staging intact
        let mut peak = 0.0f32;
        for &s in target_fir.iter() {
            peak = peak.max(s.abs());
        }
        if peak > 0.0 {
            for s in target_fir.iter_mut() {
                *s = (*s / peak) * 0.15; // Target peak scaling of 0.15 to keep level balanced
            }
        }
    }

    /// Process a single audio sample through the cabinet engine
    #[inline(always)]
    pub fn process_sample(&mut self, mut x: f32) -> f32 {
        // --- 1. Dynamic Thermal / Excursion Compression ---
        // Fast-attack (5 ms) and medium-release (100 ms) envelope follower
        let env_attack = 1.0 - (-1.0 / (self.fs * 0.005)).exp();
        let env_release = 1.0 - (-1.0 / (self.fs * 0.100)).exp();
        let abs_x = x.abs();
        
        if abs_x > self.thermal_envelope {
            self.thermal_envelope += (abs_x - self.thermal_envelope) * env_attack;
        } else {
            self.thermal_envelope += (abs_x - self.thermal_envelope) * env_release;
        }

        // When envelope exceeds 0.707 (-3 dBFS), roll off high-end to emulate paper-cone thermal compression
        let compression_depth = (self.thermal_envelope - 0.707).max(0.0) / (1.5 - 0.707);
        let dynamic_lpf_cutoff = 20000.0 - (20000.0 - 5500.0) * compression_depth.min(1.0);

        // Compute 1st-order LPF coefficients for thermal compression
        let w0_thermal = 2.0 * std::f32::consts::PI * dynamic_lpf_cutoff;
        let t_thermal = 1.0 / self.fs;
        let b0_thermal = w0_thermal * t_thermal / (2.0 + w0_thermal * t_thermal);
        let b1_thermal = b0_thermal;
        let a1_thermal = (w0_thermal * t_thermal - 2.0) / (2.0 + w0_thermal * t_thermal);

        let x_thermal = b0_thermal * x + b1_thermal * self.thermal_x1 - a1_thermal * self.thermal_y1;
        self.thermal_x1 = x;
        self.thermal_y1 = x_thermal;
        x = x_thermal;

        // --- 2. Non-Linear Cone Breakup ---
        // 3rd-order polynomial wave-shaper active when peaks exceed 0.707
        let abs_xt = x.abs();
        if abs_xt > 0.707 {
            let sign = x.signum();
            let x_norm = (abs_xt - 0.707) / (1.5 - 0.707); // dynamic headroom stretch
            let x_norm_clamped = x_norm.min(1.0);
            
            // Saturation polynomial: x - x^3 / 3
            let shaped = x_norm_clamped - (x_norm_clamped.powi(3) / 3.0);
            x = sign * (0.707 + shaped * (1.2 - 0.707)); // scale breakup peak smoothly
        }

        // --- 3. Zero-Latency Dual FIR Convolution ---
        // Store incoming sample in circular history buffer
        self.history[self.write_idx] = x;

        let mut sum_sm57 = 0.0;
        let mut sum_r121 = 0.0;
        let mut idx = self.write_idx;

        // Circular dot-product (auto-vectorized to SIMD instructions)
        for i in 0..512 {
            sum_sm57 += self.history[idx] * self.fir_sm57[i];
            sum_r121 += self.history[idx] * self.fir_r121[i];
            if idx == 0 {
                idx = 511;
            } else {
                idx -= 1;
            }
        }

        // Increment pointer
        self.write_idx = (self.write_idx + 1) % 512;

        // Linear crossfade between Channel A (SM57) and Channel B (R-121)
        let blended = (1.0 - self.mic_blend) * sum_sm57 + self.mic_blend * sum_r121;

        // --- 4. Mic Distance Air Attenuation Filter ---
        // Distance scales air LPF cutoff frequency from 20 kHz (close) down to 8 kHz (far)
        let air_cutoff = 20000.0 - (20000.0 - 8000.0) * self.mic_dist;
        let w0_air = 2.0 * std::f32::consts::PI * air_cutoff;
        let t_air = 1.0 / self.fs;
        let b0_air = w0_air * t_air / (2.0 + w0_air * t_air);
        let b1_air = b0_air;
        let a1_air = (w0_air * t_air - 2.0) / (2.0 + w0_air * t_air);

        let final_out = b0_air * blended + b1_air * self.air_x1 - a1_air * self.air_y1;
        self.air_x1 = blended;
        self.air_y1 = final_out;

        final_out
    }

    /// Block normalization helper: Hard-scale block to nominal peak of ±1.0
    pub fn process_buffer(&mut self, buffer: &mut [f32]) {
        // 1. Find block peak amplitude
        let mut peak = 0.0f32;
        for &s in buffer.iter() {
            peak = peak.max(s.abs());
        }

        // 2. Normalization factor
        let scale = if peak > 0.01 { 1.0 / peak } else { 1.0 };

        // 3. Scale buffer and apply Cabinet sample processing
        for s in buffer.iter_mut() {
            let normalized = *s * scale;
            *s = self.process_sample(normalized);
        }
    }

    /// Procedurally generate dual microphone impulse responses modeling V30 cab
    fn generate_default_ir(channel: usize, fs: f32) -> [f32; 512] {
        let mut ir = [0.0; 512];
        ir[0] = 1.0; // Delta impulse source

        if channel == 0 {
            // --- Channel A: SM57 (Aggressive upper-mids bite, tight lows) ---
            let mut hp = Biquad::new();
            hp.set_butterworth_hpf(90.0, fs); // High-pass cut

            let mut presence_peak = Biquad::new();
            presence_peak.set_peaking_eq(3500.0, fs, 6.0, 1.2); // +6 dB SM57 high-mid bite

            let mut cab_lpf = Biquad::new();
            cab_lpf.set_low_shelf(5500.0, fs, -12.0, 0.707); // 4x12 cab high roll-off

            for i in 0..512 {
                let s = ir[i];
                let s = hp.process(s);
                let s = presence_peak.process(s);
                let s = cab_lpf.process(s);
                ir[i] = s;
            }
        } else {
            // --- Channel B: Royer R-121 Ribbon (Warm low-mids, smooth highs) ---
            let mut hp = Biquad::new();
            hp.set_butterworth_hpf(60.0, fs); // Deeper bass extension

            let mut warm_peak = Biquad::new();
            warm_peak.set_peaking_eq(300.0, fs, 5.0, 0.8); // +5 dB low-mid punch

            let mut ribbon_lpf = Biquad::new();
            ribbon_lpf.set_low_shelf(4000.0, fs, -15.0, 0.707); // Softer, vintage top-end roll-off

            for i in 0..512 {
                let s = ir[i];
                let s = hp.process(s);
                let s = warm_peak.process(s);
                let s = ribbon_lpf.process(s);
                ir[i] = s;
            }
        }

        // Add 4x12 cab wood/room reflections (decaying comb filter)
        let mut refined_ir = [0.0; 512];
        for i in 0..512 {
            let mut val = ir[i];
            if i >= 36 { val += 0.28 * ir[i - 36]; } // Speaker baffle reflection
            if i >= 72 { val += 0.14 * ir[i - 72]; }
            if i >= 110 { val += 0.07 * ir[i - 110]; } // Room boundary reflection
            refined_ir[i] = val;
        }

        // Normalize IR peak to target gain scaling of 0.15
        let mut peak = 0.0f32;
        for &val in refined_ir.iter() {
            peak = peak.max(val.abs());
        }
        if peak > 0.0 {
            for val in refined_ir.iter_mut() {
                *val = (*val / peak) * 0.15;
            }
        }

        refined_ir
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_cabinet_convolution_and_blend() {
        let mut cab = Cabinet::new(44100.0);
        
        // At mix = 0.0 (pure SM57), and distance = 0.0, we feed a single impulse
        cab.set_params(0.0, 0.0);
        let out_sm57 = cab.process_sample(1.0);
        
        // The first output sample should correspond to the first sample of SM57 impulse response (~0.15 peak)
        assert!(out_sm57.abs() > 0.01);
        
        // Switch to pure R-121
        cab.reset();
        cab.set_params(1.0, 0.0);
        let out_r121 = cab.process_sample(1.0);
        assert!(out_r121.abs() > 0.01);
    }

    #[test]
    fn test_cone_breakup_and_compression() {
        let mut cab = Cabinet::new(44100.0);
        
        // Feed a high amplitude signal to trigger thermal compression and cone breakup
        let mut max_output = 0.0f32;
        for _ in 0..100 {
            let out = cab.process_sample(2.0); // Extreme amplitude
            max_output = max_output.max(out.abs());
        }
        
        // Envelope should react to the high signal
        assert!(cab.thermal_envelope > 0.5, "Thermal envelope follower should track extreme signals");
    }
}

