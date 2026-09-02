use crate::biquad::Biquad;
use crate::oversampler::Oversampler2x;

/// A single phenomenological 12AX7 vacuum tube triode model.
/// Simulates grid conduction, dynamic bias shift (excursion), and asymmetric clipping.
#[derive(Debug, Clone, Copy)]
pub struct TriodeStage {
    envelope: f32,
    static_bias: f32,
    asymmetry: f32,
    attack_coeff: f32,
    decay_coeff: f32,
}

impl TriodeStage {
    /// Create a new triode stage.
    /// static_bias: operating point (typically negative, e.g. -0.15)
    /// asymmetry: scales negative cutoff (typically < 1.0, e.g. 0.6)
    /// fs: sample rate of the parent pipeline
    pub fn new(static_bias: f32, asymmetry: f32, fs: f32) -> Self {
        // Runs inside a 2x oversampler, so effective sample rate is 2 * fs
        let oversampled_fs = 2.0 * fs;
        let decay_coeff = 1.0 - (-1.0 / (oversampled_fs * 0.050)).exp(); // 50 ms decay
        let attack_coeff = 1.0 - (-1.0 / (oversampled_fs * 0.001)).exp(); // 1 ms attack (grid conduction rate)
        
        Self {
            envelope: 0.0,
            static_bias,
            asymmetry,
            attack_coeff,
            decay_coeff,
        }
    }

    /// Reset internal state
    pub fn reset(&mut self) {
        self.envelope = 0.0;
    }

    /// Update the coefficients for a new sample rate
    pub fn set_sample_rate(&mut self, fs: f32) {
        let oversampled_fs = 2.0 * fs;
        self.decay_coeff = 1.0 - (-1.0 / (oversampled_fs * 0.050)).exp();
        self.attack_coeff = 1.0 - (-1.0 / (oversampled_fs * 0.001)).exp();
    }

    /// Process a single sample through the triode waveshaper.
    /// Expects input in the nominal ±1.0 range (though overdriven signals can exceed this).
    #[inline(always)]
    pub fn process(&mut self, x: f32) -> f32 {
        // Envelope follower tracks absolute amplitude of the grid-to-cathode voltage
        let input_abs = x.abs();
        if input_abs > self.envelope {
            self.envelope += (input_abs - self.envelope) * self.attack_coeff;
        } else {
            self.envelope += (input_abs - self.envelope) * self.decay_coeff;
        }

        // Dynamic Bias Shift: large signals push the operating point colder (more negative)
        // to simulate charge buildup on the coupling/bypass capacitors (grid-leak/cathode sag).
        let dynamic_bias = -0.5 * self.envelope;
        let total_bias = self.static_bias + dynamic_bias;

        // Apply asymmetric soft clipping
        // Positive grid voltage clips early and hard (grid current saturation).
        // Negative grid voltage enters soft cutoff (gently tapering off to zero).
        let v_g = x + total_bias;
        let y = if v_g > 0.0 {
            v_g.tanh()
        } else {
            (v_g * self.asymmetry).tanh() / self.asymmetry
        };

        // Subtract the idle output (offset correction) to prevent static DC leak.
        let y_idle = if total_bias > 0.0 {
            total_bias.tanh()
        } else {
            (total_bias * self.asymmetry).tanh() / self.asymmetry
        };

        y - y_idle
    }
}

/// The main Preamp Stage of the JPM 8000 amplifier.
/// Cascades pre-EQ filtering, gain scaling, and two oversampled 12AX7 triodes.
#[derive(Debug, Clone, Copy)]
pub struct Preamp {
    hpf: Biquad,
    pre_gain: f32,
    stage1: TriodeStage,
    stage1_oversampler: Oversampler2x,
    stage2: TriodeStage,
    stage2_oversampler: Oversampler2x,
    coupling_hpf1: Biquad,
    coupling_hpf2: Biquad,
}

impl Preamp {
    pub fn new(fs: f32) -> Self {
        let mut hpf = Biquad::new();
        hpf.set_butterworth_hpf(100.0, fs); // Pre-EQ 100 Hz cut

        // Standard 12AX7 cascade parameters
        // Stage 1: slightly warmer bias for asymmetric clipping
        let stage1 = TriodeStage::new(-0.15, 0.6, fs);
        // Stage 2: colder bias for aggressive cutoff distortion
        let stage2 = TriodeStage::new(-0.25, 0.5, fs);

        // Inter-stage coupling filters (1st-order high-pass filters to shape low end and block DC)
        let mut coupling_hpf1 = Biquad::new();
        coupling_hpf1.set_butterworth_hpf(80.0, fs);

        let mut coupling_hpf2 = Biquad::new();
        coupling_hpf2.set_butterworth_hpf(80.0, fs);

        Self {
            hpf,
            pre_gain: 1.0, // Default gain is unity (x1.0)
            stage1,
            stage1_oversampler: Oversampler2x::new(),
            stage2,
            stage2_oversampler: Oversampler2x::new(),
            coupling_hpf1,
            coupling_hpf2,
        }
    }

    /// Reset internal filter states and triodes
    pub fn reset(&mut self) {
        self.hpf.reset();
        self.stage1.reset();
        self.stage1_oversampler.reset();
        self.stage2.reset();
        self.stage2_oversampler.reset();
        self.coupling_hpf1.reset();
        self.coupling_hpf2.reset();
    }

    /// Update sample rate for all internal biquads and oversampled states
    pub fn set_sample_rate(&mut self, fs: f32) {
        self.hpf.set_butterworth_hpf(100.0, fs);
        self.coupling_hpf1.set_butterworth_hpf(80.0, fs);
        self.coupling_hpf2.set_butterworth_hpf(80.0, fs);
        self.stage1.set_sample_rate(fs);
        self.stage2.set_sample_rate(fs);
    }

    /// Set preamp drive gain (scales from 0.0 to 10.0)
    pub fn set_pre_gain(&mut self, gain: f32) {
        self.pre_gain = gain.clamp(0.0, 10.0);
    }

    /// Process a single audio sample through the preamp signal chain
    #[inline(always)]
    pub fn process_sample(&mut self, x: f32) -> f32 {
        // 1. High-Pass Filter: 2nd-order Butterworth HPF at 100 Hz
        let x_hpf = self.hpf.process(x);

        // 2. Pre-Gain Scaling (0.0x to 10.0x)
        let x_scaled = x_hpf * self.pre_gain;

        // 3. Stage 1 Triode: 2x Oversampled Non-Linearity
        let x_up1 = self.stage1_oversampler.upsample(x_scaled);
        let y_up1 = [
            self.stage1.process(x_up1[0]),
            self.stage1.process(x_up1[1]),
        ];
        let y_stage1 = self.stage1_oversampler.downsample(y_up1);

        // 4. Inter-stage coupling capacitor (blocks DC drift)
        let y_coupled = self.coupling_hpf1.process(y_stage1);

        // 5. Stage 2 Triode: 2x Oversampled Non-Linearity
        let x_up2 = self.stage2_oversampler.upsample(y_coupled);
        let y_up2 = [
            self.stage2.process(x_up2[0]),
            self.stage2.process(x_up2[1]),
        ];
        let y_stage2 = self.stage2_oversampler.downsample(y_up2);

        // 6. Post-preamp DC coupling filter
        self.coupling_hpf2.process(y_stage2)
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_preamp_clipping_and_gain() {
        let mut preamp = Preamp::new(44100.0);
        
        // At unity gain, a very small input should remain virtually linear and small
        preamp.set_pre_gain(1.0);
        let out_small = preamp.process_sample(0.01);
        assert!(out_small.abs() < 0.02);
        
        // High gain should clip the signal, so the output shouldn't exceed nominal 1.0 significantly
        preamp.reset();
        preamp.set_pre_gain(10.0);
        let mut max_out = 0.0f32;
        for _ in 0..100 {
            let out_large = preamp.process_sample(0.8);
            max_out = max_out.max(out_large.abs());
        }
        assert!(max_out < 1.6, "Output should soft-clip and not explode, got {}", max_out);
    }

    #[test]
    fn test_triode_bias_shift() {
        let mut stage = TriodeStage::new(-0.2, 0.6, 44100.0);
        
        // Before playing, envelope should be 0
        assert_eq!(stage.envelope, 0.0);
        
        // Feed large positive inputs to trigger grid conduction and bias shift
        for _ in 0..100 {
            let _ = stage.process(1.0);
        }
        
        // Dynamic envelope should have grown significantly
        assert!(stage.envelope > 0.1, "Envelope follower should track strong inputs");
    }
}

