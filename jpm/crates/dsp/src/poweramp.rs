use crate::biquad::Biquad;
use crate::oversampler::Oversampler2x;

/// A model of an EL34 push-pull vacuum tube power amplifier.
/// Simulates Phase Inverter (PI) saturation, asymmetric EL34 soft clipping,
/// dynamic power supply sag, and unified reactive speaker impedance loading.
#[derive(Debug, Clone, Copy)]
pub struct PowerAmp {
    // Overdriven non-linear stage states
    oversampler: Oversampler2x,
    sag_envelope: f32,
    sag_coeff: f32,
    sag_factor: f32, // Strength of power supply sag (0.0 = none, e.g. 0.3 = normal)

    // Reactive load filters
    low_resonance_filter: Biquad,
    inductive_rise_filter: Biquad,
    
    // Controls
    master_gain: f32, // Controls drive into the power amp (0.0 to 2.0)
    resonance: f32,   // 0.0 to 1.0 (scales low resonance peak gain +0 to +10 dB)
    presence: f32,    // 0.0 to 1.0 (scales high presence shelf gain +0 to +8 dB)
    fs: f32,
}

impl PowerAmp {
    pub fn new(fs: f32) -> Self {
        let sag_coeff = 1.0 - (-1.0 / (fs * 0.050)).exp(); // 50 ms time constant for supply recovery

        let mut amp = Self {
            oversampler: Oversampler2x::new(),
            sag_envelope: 0.0,
            sag_coeff,
            sag_factor: 0.25, // 25% max power supply sag depth
            low_resonance_filter: Biquad::new(),
            inductive_rise_filter: Biquad::new(),
            master_gain: 1.0,
            resonance: 0.5,
            presence: 0.5,
            fs,
        };

        amp.update_reactive_load();
        amp
    }

    /// Reset all filter states, oversamplers, and power sag history to avoid clicks
    pub fn reset(&mut self) {
        self.oversampler.reset();
        self.sag_envelope = 0.0;
        self.low_resonance_filter.reset();
        self.inductive_rise_filter.reset();
    }

    /// Set a new sample rate and update internal coefficients
    pub fn set_sample_rate(&mut self, fs: f32) {
        self.fs = fs;
        self.sag_coeff = 1.0 - (-1.0 / (fs * 0.050)).exp();
        self.update_reactive_load();
    }

    /// Set power amp controls
    /// master: drive gain [0.0, 2.0]
    /// resonance: peak gain scaling [0.0, 1.0]
    /// presence: shelf gain scaling [0.0, 1.0]
    pub fn set_params(&mut self, master: f32, resonance: f32, presence: f32) {
        self.master_gain = master.clamp(0.0, 2.0);
        self.resonance = resonance.clamp(0.0, 1.0);
        self.presence = presence.clamp(0.0, 1.0);
        self.update_reactive_load();
    }

    /// Update reactive speaker load coefficients based on Presence and Resonance settings
    fn update_reactive_load(&mut self) {
        // Low Resonance: Peaking biquad at 85 Hz (Q = 2.0)
        // Resonance knob scales gain from +0 dB to +10 dB
        let resonance_gain_db = self.resonance * 10.0;
        self.low_resonance_filter.set_peaking_eq(85.0, self.fs, resonance_gain_db, 2.0);

        // Inductive High Rise: High-shelf biquad at 3.0 kHz
        // Presence knob scales gain from +0 dB to +8 dB
        let presence_gain_db = self.presence * 8.0;
        self.inductive_rise_filter.set_high_shelf(3000.0, self.fs, presence_gain_db, 0.707);
    }

    /// Process a single audio sample through the power amplifier signal chain
    #[inline(always)]
    pub fn process_sample(&mut self, x: f32) -> f32 {
        // Scale input by master volume to drive power tubes
        let x_driven = x * self.master_gain;

        // Apply 2x oversampling to the non-linear Phase Inverter & Power Tube clipping stages
        let x_up = self.oversampler.upsample(x_driven);
        let mut y_up = [0.0; 2];

        for i in 0..2 {
            let sample = x_up[i];

            // 1. Phase Inverter Saturation: Algebraic soft-clipper
            let pi_out = sample / (1.0 + sample.abs());

            // 2. Power Tube Saturation (EL34 Push-Pull with dynamic power supply sag)
            // Envelope follower tracks absolute output signal amplitude to calculate power draw sag
            let abs_out = pi_out.abs();
            self.sag_envelope += (abs_out - self.sag_envelope) * self.sag_coeff;
            
            let sag_amount = self.sag_envelope * self.sag_factor;
            let headroom = 1.0 - sag_amount.clamp(0.0, 0.5); // Max sag drops plate voltage to 50%

            // Under sag, the effective tube headroom decreases.
            // This compresses the output and drives the tubes into heavier asymmetric saturation.
            let el34_input = pi_out / headroom;
            let el34_clipped = if el34_input > 0.0 {
                // Positive half-wave: clipping on one side of the push-pull stage
                (el34_input * 1.1).tanh() / 1.1
            } else {
                // Negative half-wave: clipping on the other side, slightly asymmetric
                (el34_input * 0.8).tanh() / 0.8
            };

            // Scale back down by current dynamic plate voltage headroom
            y_up[i] = el34_clipped * headroom;
        }

        // Downsample the non-linear stage output to base rate
        let y_down = self.oversampler.downsample(y_up);

        // 3. Unified Reactive Speaker Impedance Coupling (Linear stages, no oversampling)
        let y_resonance = self.low_resonance_filter.process(y_down);
        self.inductive_rise_filter.process(y_resonance)
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_power_amp_clipping_and_sag() {
        let mut power_amp = PowerAmp::new(44100.0);
        
        // Before processing, sag envelope should be 0.0
        assert_eq!(power_amp.sag_envelope, 0.0);
        
        // Feed extremely high volume signal to drive PI and EL34 power tubes into saturation
        power_amp.set_params(2.0, 0.0, 0.0); // Master gain at max (2.0)
        
        for _ in 0..1000 {
            let _ = power_amp.process_sample(1.0);
        }
        
        // Dynamic power supply sag should be active, meaning the sag envelope follower has reacted
        assert!(power_amp.sag_envelope > 0.1, "Power supply sag should respond to heavy continuous power tube load");
    }

    #[test]
    fn test_reactive_speaker_impedance_coupling() {
        let mut power_amp = PowerAmp::new(44100.0);
        
        // When presence is 0.0, presence filter gain is 0 dB (unity gain)
        // When presence is 1.0, presence high-shelf filter has +8 dB gain
        power_amp.set_params(1.0, 0.0, 1.0);
        
        // Test high frequency boost
        // Feed a high-frequency sine wave (e.g., 6000 Hz)
        let sample_rate = 44100.0;
        let mut out_boosted = 0.0f32;
        for i in 0..100 {
            let t = i as f32 / sample_rate;
            let x = (2.0 * std::f32::consts::PI * 6000.0 * t).sin();
            let y = power_amp.process_sample(x);
            out_boosted = out_boosted.max(y.abs());
        }
        
        // With presence at 1.0, output should be amplified
        power_amp.reset();
        power_amp.set_params(1.0, 0.0, 0.0); // presence at 0.0
        let mut out_flat = 0.0f32;
        for i in 0..100 {
            let t = i as f32 / sample_rate;
            let x = (2.0 * std::f32::consts::PI * 6000.0 * t).sin();
            let y = power_amp.process_sample(x);
            out_flat = out_flat.max(y.abs());
        }
        
        assert!(out_boosted > out_flat, "Presence boost should amplify high frequencies: boosted {} vs flat {}", out_boosted, out_flat);
    }
}

