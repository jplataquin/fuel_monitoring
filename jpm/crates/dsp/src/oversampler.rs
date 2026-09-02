/// A 2x oversampler using a 15-tap half-band FIR filter.
/// Provides linear-phase filtering for anti-imaging (upsampling) and anti-aliasing (downsampling).
#[derive(Debug, Clone, Copy)]
pub struct Oversampler2x {
    up_history: [f32; 15],
    down_history: [f32; 15],
}

impl Oversampler2x {
    pub fn new() -> Self {
        Self {
            up_history: [0.0; 15],
            down_history: [0.0; 15],
        }
    }

    /// Reset all filter states to zero.
    pub fn reset(&mut self) {
        self.up_history = [0.0; 15];
        self.down_history = [0.0; 15];
    }

    /// Takes a single input sample at the base rate, upsamples it by 2x,
    /// and returns 2 samples at the oversampled rate.
    #[inline(always)]
    pub fn upsample(&mut self, x: f32) -> [f32; 2] {
        const FIR_COEFFS: [f32; 15] = [
            -0.007, 0.0, 0.024, 0.0, -0.071, 0.0, 0.304, 0.5,
             0.304, 0.0, -0.071, 0.0, 0.024, 0.0, -0.007
        ];

        // 1st sub-sample (insert x)
        self.up_history.copy_within(0..14, 1);
        self.up_history[0] = x;
        let mut sum0 = 0.0;
        for i in 0..15 {
            sum0 += self.up_history[i] * FIR_COEFFS[i];
        }

        // 2nd sub-sample (insert 0.0)
        self.up_history.copy_within(0..14, 1);
        self.up_history[0] = 0.0;
        let mut sum1 = 0.0;
        for i in 0..15 {
            sum1 += self.up_history[i] * FIR_COEFFS[i];
        }

        // Since we are inserting zeros, we multiply by 2.0 to preserve the original energy level.
        [sum0 * 2.0, sum1 * 2.0]
    }

    /// Takes 2 samples at the oversampled rate, applies anti-aliasing filtering,
    /// and returns 1 downsampled sample at the base rate.
    #[inline(always)]
    pub fn downsample(&mut self, y: [f32; 2]) -> f32 {
        const FIR_COEFFS: [f32; 15] = [
            -0.007, 0.0, 0.024, 0.0, -0.071, 0.0, 0.304, 0.5,
             0.304, 0.0, -0.071, 0.0, 0.024, 0.0, -0.007
        ];

        // Push 1st oversampled sample
        self.down_history.copy_within(0..14, 1);
        self.down_history[0] = y[0];

        // Push 2nd oversampled sample
        self.down_history.copy_within(0..14, 1);
        self.down_history[0] = y[1];

        // Compute LPF output
        let mut sum = 0.0;
        for i in 0..15 {
            sum += self.down_history[i] * FIR_COEFFS[i];
        }
        sum
    }
}
