use crate::biquad::Biquad;

/// Interactive 3-band passive Marshall EQ network (Bass, Middle, Treble).
/// Models the interdependence and frequency shifting characteristic of passive guitar tone stacks.
#[derive(Debug, Clone, Copy)]
pub struct ToneStack {
    bass_filter: Biquad,
    mid_filter: Biquad,
    treble_filter: Biquad,
    fs: f32,
    bass_val: f32,   // 0.0 to 1.0
    mid_val: f32,    // 0.0 to 1.0
    treble_val: f32, // 0.0 to 1.0
}

impl ToneStack {
    /// Create a new interactive ToneStack
    pub fn new(fs: f32) -> Self {
        let mut stack = Self {
            bass_filter: Biquad::new(),
            mid_filter: Biquad::new(),
            treble_filter: Biquad::new(),
            fs,
            bass_val: 0.5,
            mid_val: 0.5,
            treble_val: 0.5,
        };
        stack.update_filters();
        stack
    }

    /// Reset internal filter state histories to avoid clicks
    pub fn reset(&mut self) {
        self.bass_filter.reset();
        self.mid_filter.reset();
        self.treble_filter.reset();
    }

    /// Set a new sample rate and update filter coefficients
    pub fn set_sample_rate(&mut self, fs: f32) {
        self.fs = fs;
        self.update_filters();
    }

    /// Set UI parameters and recalculate biquad coefficients
    /// bass, mid, treble are clamped in [0.0, 1.0]
    pub fn set_params(&mut self, bass: f32, mid: f32, treble: f32) {
        self.bass_val = bass.clamp(0.0, 1.0);
        self.mid_val = mid.clamp(0.0, 1.0);
        self.treble_val = treble.clamp(0.0, 1.0);
        self.update_filters();
    }

    /// Recalculates the coefficients for Low Shelf, Peaking Mid, and High Shelf biquads.
    /// Mimics Marshall passive network loading interactions:
    /// - Mid notch/peak frequency shifts dynamically based on Mid knob setting (interactive frequency shift).
    /// - High mid boosts slightly interact with treble shelf response.
    /// - Bass and mid controls interactively load and reduce each other's gains under heavy settings.
    fn update_filters(&mut self) {
        // 1. Bass (100 Hz Low Shelf)
        // Maps [0.0, 1.0] to [-12.0, +12.0] dB.
        // Interactivity: Heavy mid settings slightly load the passive low-shelf circuit, tightening low end.
        let bass_interaction = 3.0 * self.mid_val;
        let bass_gain_db = -12.0 + (24.0 * self.bass_val) - bass_interaction;
        self.bass_filter.set_low_shelf(100.0, self.fs, bass_gain_db, 0.707);

        // 2. Middle (Notch/Peak at ~650 Hz with interactive frequency shift)
        // Frequency shifts between 500 Hz (at 0.0) and 800 Hz (at 1.0) depending on Mid knob.
        let mid_fc = 500.0 + (300.0 * self.mid_val);
        // Maps [0.0, 1.0] to [-18.0, +6.0] dB.
        // Interactivity: High bass settings load the mid notch depth.
        let mid_interaction = 4.0 * self.bass_val;
        let mid_gain_db = -18.0 + (24.0 * self.mid_val) - mid_interaction;
        self.mid_filter.set_peaking_eq(mid_fc, self.fs, mid_gain_db, 0.5); // Wider Q for a smooth, natural mid scoop

        // 3. Treble (2.5 kHz High Shelf with interactive frequency shift)
        // Cutoff shifts from 2.0 kHz to 3.0 kHz depending on Treble knob.
        let treble_fc = 2000.0 + (1000.0 * self.treble_val);
        // Maps [0.0, 1.0] to [-12.0, +12.0] dB.
        // Interactivity: High mid boost slightly lifts Treble presence.
        let treble_interaction = 2.0 * self.mid_val;
        let treble_gain_db = -12.0 + (24.0 * self.treble_val) + treble_interaction;
        self.treble_filter.set_high_shelf(treble_fc, self.fs, treble_gain_db, 0.707);
    }

    /// Process a single audio sample through the interactive EQ cascade.
    #[inline(always)]
    pub fn process_sample(&mut self, x: f32) -> f32 {
        let x_bass = self.bass_filter.process(x);
        let x_mid = self.mid_filter.process(x_bass);
        self.treble_filter.process(x_mid)
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_tone_stack_bass_boost() {
        let mut tone_stack = ToneStack::new(44100.0);
        let sample_rate = 44100.0;
        
        // Let's test with a low-frequency sine wave (e.g., 80 Hz)
        // With bass at 1.0, low frequency output should be boosted compared to bass at 0.0
        tone_stack.set_params(1.0, 0.5, 0.5);
        let mut out_boosted = 0.0f32;
        for i in 0..100 {
            let t = i as f32 / sample_rate;
            let x = (2.0 * std::f32::consts::PI * 80.0 * t).sin();
            let y = tone_stack.process_sample(x);
            out_boosted = out_boosted.max(y.abs());
        }
        
        tone_stack.reset();
        tone_stack.set_params(0.0, 0.5, 0.5);
        let mut out_cut = 0.0f32;
        for i in 0..100 {
            let t = i as f32 / sample_rate;
            let x = (2.0 * std::f32::consts::PI * 80.0 * t).sin();
            let y = tone_stack.process_sample(x);
            out_cut = out_cut.max(y.abs());
        }
        
        assert!(out_boosted > out_cut, "Bass boost should increase low frequencies: boosted {} vs cut {}", out_boosted, out_cut);
    }

    #[test]
    fn test_tone_stack_treble_boost() {
        let mut tone_stack = ToneStack::new(44100.0);
        let sample_rate = 44100.0;
        
        // Let's test with a high-frequency sine wave (e.g., 5000 Hz)
        // With treble at 1.0, high frequency output should be boosted compared to treble at 0.0
        tone_stack.set_params(0.5, 0.5, 1.0);
        let mut out_boosted = 0.0f32;
        for i in 0..100 {
            let t = i as f32 / sample_rate;
            let x = (2.0 * std::f32::consts::PI * 5000.0 * t).sin();
            let y = tone_stack.process_sample(x);
            out_boosted = out_boosted.max(y.abs());
        }
        
        tone_stack.reset();
        tone_stack.set_params(0.5, 0.5, 0.0);
        let mut out_cut = 0.0f32;
        for i in 0..100 {
            let t = i as f32 / sample_rate;
            let x = (2.0 * std::f32::consts::PI * 5000.0 * t).sin();
            let y = tone_stack.process_sample(x);
            out_cut = out_cut.max(y.abs());
        }
        
        assert!(out_boosted > out_cut, "Treble boost should increase high frequencies: boosted {} vs cut {}", out_boosted, out_cut);
    }
}

