/// A feedback comb filter used inside the algorithmic reverb.
#[derive(Debug, Clone, Copy)]
struct CombFilter<const MAX_SIZE: usize> {
    buffer: [f32; MAX_SIZE],
    size: usize,
    write_idx: usize,
    feedback: f32,
    filter_state: f32,
    damp: f32,
}

impl<const MAX_SIZE: usize> CombFilter<MAX_SIZE> {
    pub fn new() -> Self {
        Self {
            buffer: [0.0; MAX_SIZE],
            size: MAX_SIZE,
            write_idx: 0,
            feedback: 0.5,
            filter_state: 0.0,
            damp: 0.2,
        }
    }

    pub fn set_size(&mut self, size: usize) {
        self.size = size.min(MAX_SIZE);
        if self.write_idx >= self.size {
            self.write_idx = 0;
        }
    }

    pub fn reset(&mut self) {
        self.buffer = [0.0; MAX_SIZE];
        self.write_idx = 0;
        self.filter_state = 0.0;
    }

    #[inline(always)]
    pub fn process(&mut self, x: f32) -> f32 {
        let output = self.buffer[self.write_idx];
        
        // Low-pass feedback filter (dampening)
        self.filter_state = (output * (1.0 - self.damp)) + (self.filter_state * self.damp);
        
        // Feedback into the delay line
        self.buffer[self.write_idx] = x + (self.filter_state * self.feedback);
        
        // Increment pointer
        self.write_idx = (self.write_idx + 1) % self.size;
        
        output
    }
}

/// A feedback allpass filter used inside the algorithmic reverb.
#[derive(Debug, Clone, Copy)]
struct AllpassFilter<const MAX_SIZE: usize> {
    buffer: [f32; MAX_SIZE],
    size: usize,
    write_idx: usize,
    feedback: f32,
}

impl<const MAX_SIZE: usize> AllpassFilter<MAX_SIZE> {
    pub fn new() -> Self {
        Self {
            buffer: [0.0; MAX_SIZE],
            size: MAX_SIZE,
            write_idx: 0,
            feedback: 0.5,
        }
    }

    pub fn set_size(&mut self, size: usize) {
        self.size = size.min(MAX_SIZE);
        if self.write_idx >= self.size {
            self.write_idx = 0;
        }
    }

    pub fn reset(&mut self) {
        self.buffer = [0.0; MAX_SIZE];
        self.write_idx = 0;
    }

    #[inline(always)]
    pub fn process(&mut self, x: f32) -> f32 {
        let buf_val = self.buffer[self.write_idx];
        
        // Standard Schroeder-allpass equation: y = -g * x + x_delay + g * y_delay
        let output = -self.feedback * x + buf_val;
        
        self.buffer[self.write_idx] = x + (buf_val * self.feedback);
        
        self.write_idx = (self.write_idx + 1) % self.size;
        
        output
    }
}

/// A highly musical, zero-heap-allocation algorithmic Reverb processor.
/// Built using a Freeverb-inspired topology: 8 parallel Comb Filters followed by 4 series Allpass Filters.
/// All delay line buffers are pre-allocated statically for the maximum supported sample rate (96 kHz).
#[derive(Debug, Clone)]
pub struct Reverb {
    // 8 Parallel Comb Filters
    comb1: CombFilter<2500>,
    comb2: CombFilter<2700>,
    comb3: CombFilter<2900>,
    comb4: CombFilter<3100>,
    comb5: CombFilter<3300>,
    comb6: CombFilter<3400>,
    comb7: CombFilter<3600>,
    comb8: CombFilter<3700>,

    // 4 Series Allpass Filters
    allpass1: AllpassFilter<1300>,
    allpass2: AllpassFilter<1000>,
    allpass3: AllpassFilter<800>,
    allpass4: AllpassFilter<550>,

    // Controls
    mix: f32,       // 0.0 (dry) to 1.0 (wet)
    room_size: f32, // 0.0 to 1.0 (controls decay time)
    damp: f32,      // 0.0 to 1.0 (controls high frequency absorption)
    fs: f32,
}

impl Reverb {
    pub fn new(fs: f32) -> Self {
        let mut reverb = Self {
            comb1: CombFilter::new(),
            comb2: CombFilter::new(),
            comb3: CombFilter::new(),
            comb4: CombFilter::new(),
            comb5: CombFilter::new(),
            comb6: CombFilter::new(),
            comb7: CombFilter::new(),
            comb8: CombFilter::new(),

            allpass1: AllpassFilter::new(),
            allpass2: AllpassFilter::new(),
            allpass3: AllpassFilter::new(),
            allpass4: AllpassFilter::new(),

            mix: 0.25,      // Standard FX loop wet mix
            room_size: 0.7, // Medium room decay
            damp: 0.2,      // Natural wood wall dampening
            fs,
        };

        reverb.update_delay_lines();
        reverb.update_coefficients();
        reverb
    }

    /// Reset all internal delay line buffers and filter memories to avoid clicks
    pub fn reset(&mut self) {
        self.comb1.reset();
        self.comb2.reset();
        self.comb3.reset();
        self.comb4.reset();
        self.comb5.reset();
        self.comb6.reset();
        self.comb7.reset();
        self.comb8.reset();

        self.allpass1.reset();
        self.allpass2.reset();
        self.allpass3.reset();
        self.allpass4.reset();
    }

    /// Set a new sample rate, scaling the active delay line sizes proportionally
    pub fn set_sample_rate(&mut self, fs: f32) {
        self.fs = fs;
        self.update_delay_lines();
    }

    /// Set reverb control parameters
    /// mix: [0.0, 1.0]
    /// room_size: [0.0, 1.0]
    /// damp: [0.0, 1.0]
    pub fn set_params(&mut self, mix: f32, room_size: f32, damp: f32) {
        self.mix = mix.clamp(0.0, 1.0);
        self.room_size = room_size.clamp(0.0, 1.0);
        self.damp = damp.clamp(0.0, 1.0);
        self.update_coefficients();
    }

    /// Update delay line active lengths based on sample rate
    /// Uses Freeverb base lengths at 44.1 kHz, scaled linearly
    fn update_delay_lines(&mut self) {
        let scale = self.fs / 44100.0;

        self.comb1.set_size((1116.0 * scale) as usize);
        self.comb2.set_size((1188.0 * scale) as usize);
        self.comb3.set_size((1277.0 * scale) as usize);
        self.comb4.set_size((1356.0 * scale) as usize);
        self.comb5.set_size((1422.0 * scale) as usize);
        self.comb6.set_size((1491.0 * scale) as usize);
        self.comb7.set_size((1557.0 * scale) as usize);
        self.comb8.set_size((1617.0 * scale) as usize);

        self.allpass1.set_size((556.0 * scale) as usize);
        self.allpass2.set_size((441.0 * scale) as usize);
        self.allpass3.set_size((341.0 * scale) as usize);
        self.allpass4.set_size((225.0 * scale) as usize);
    }

    /// Recalculate filter feedbacks and absorption damping coefficients
    fn update_coefficients(&mut self) {
        // Map room_size parameter [0.0, 1.0] to feedback range [0.7, 0.98]
        let comb_feedback = 0.7 + (self.room_size * 0.28);
        
        // Map damp parameter [0.0, 1.0] to damping factor [0.0, 0.4]
        let comb_damping = self.damp * 0.4;

        // Apply comb filter gains
        self.comb1.feedback = comb_feedback; self.comb1.damp = comb_damping;
        self.comb2.feedback = comb_feedback; self.comb2.damp = comb_damping;
        self.comb3.feedback = comb_feedback; self.comb3.damp = comb_damping;
        self.comb4.feedback = comb_feedback; self.comb4.damp = comb_damping;
        self.comb5.feedback = comb_feedback; self.comb5.damp = comb_damping;
        self.comb6.feedback = comb_feedback; self.comb6.damp = comb_damping;
        self.comb7.feedback = comb_feedback; self.comb7.damp = comb_damping;
        self.comb8.feedback = comb_feedback; self.comb8.damp = comb_damping;

        // Series Allpass filters have fixed feedback coefficients for maximum diffusion
        self.allpass1.feedback = 0.5;
        self.allpass2.feedback = 0.5;
        self.allpass3.feedback = 0.5;
        self.allpass4.feedback = 0.5;
    }

    /// Process a single audio sample through the algorithmic reverb
    #[inline(always)]
    pub fn process_sample(&mut self, x: f32) -> f32 {
        if self.mix <= 0.001 {
            return x; // Short circuit for 100% dry
        }

        // Parallel Comb filters process
        let comb_out = self.comb1.process(x)
            + self.comb2.process(x)
            + self.comb3.process(x)
            + self.comb4.process(x)
            + self.comb5.process(x)
            + self.comb6.process(x)
            + self.comb7.process(x)
            + self.comb8.process(x);

        // Normalize sum of comb filters
        let comb_scaled = comb_out * 0.125;

        // Series Allpass diffusion
        let ap1 = self.allpass1.process(comb_scaled);
        let ap2 = self.allpass2.process(ap1);
        let ap3 = self.allpass3.process(ap2);
        let wet = self.allpass4.process(ap3);

        // Equal-power crossfade or direct mix to preserve signal levels
        // We use an equal-power crossfade representation for smooth wet/dry integration:
        let dry_gain = (1.0 - self.mix).sqrt();
        let wet_gain = self.mix.sqrt();

        (x * dry_gain) + (wet * wet_gain)
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_reverb_dry_wet_mix() {
        let mut reverb = Reverb::new(44100.0);
        
        // At mix = 0.0 (pure dry), processing a sample should return exactly the input
        reverb.set_params(0.0, 0.5, 0.2);
        let input = 0.5f32;
        let out_dry = reverb.process_sample(input);
        assert_eq!(out_dry, input);
        
        // At mix = 1.0 (pure wet), processing a sample should produce a wet signal (which will be 0 on the very first sample due to buffer delay, representing standard delay behavior!)
        reverb.reset();
        reverb.set_params(1.0, 0.5, 0.2);
        let out_wet = reverb.process_sample(input);
        assert_eq!(out_wet, 0.0); // No feedforward, so first output sample must be 0 (only feedback)
    }

    #[test]
    fn test_reverb_decay_tail() {
        let mut reverb = Reverb::new(44100.0);
        reverb.set_params(0.5, 0.9, 0.1); // High room size/decay
        
        // Feed a single impulse
        let _ = reverb.process_sample(1.0);
        
        // Check that subsequent samples are non-zero (reverb tail decaying over time)
        let mut tail_energy = 0.0f32;
        for _ in 0..10000 {
            let out = reverb.process_sample(0.0);
            tail_energy += out.abs();
        }
        assert!(tail_energy > 0.01, "Reverb should produce a decaying feedback tail");
    }
}

