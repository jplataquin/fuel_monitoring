/// A generic, numerically stable Direct Form I Biquad filter.
/// Suitable for all standard audio filtering tasks.
#[derive(Debug, Clone, Copy)]
pub struct Biquad {
    pub b0: f32,
    pub b1: f32,
    pub b2: f32,
    pub a1: f32,
    pub a2: f32,
    
    // Direct Form I state variables
    x1: f32,
    x2: f32,
    y1: f32,
    y2: f32,
}

impl Biquad {
    pub fn new() -> Self {
        Self {
            b0: 1.0, b1: 0.0, b2: 0.0,
            a1: 0.0, a2: 0.0,
            x1: 0.0, x2: 0.0,
            y1: 0.0, y2: 0.0,
        }
    }

    /// Reset filter state variables to prevent clicks
    pub fn reset(&mut self) {
        self.x1 = 0.0;
        self.x2 = 0.0;
        self.y1 = 0.0;
        self.y2 = 0.0;
    }

    /// Process a single sample through the biquad filter
    #[inline(always)]
    pub fn process(&mut self, x: f32) -> f32 {
        let y = self.b0 * x + self.b1 * self.x1 + self.b2 * self.x2 - self.a1 * self.y1 - self.a2 * self.y2;
        
        // Push state
        self.x2 = self.x1;
        self.x1 = x;
        self.y2 = self.y1;
        self.y1 = y;
        
        y
    }

    /// Set coefficients for a 2nd-order Butterworth High-Pass Filter (HPF)
    pub fn set_butterworth_hpf(&mut self, fc: f32, fs: f32) {
        let omega = 2.0 * std::f32::consts::PI * fc / fs;
        let cos_w = omega.cos();
        let sin_w = omega.sin();
        let q = 1.0 / std::f32::consts::FRAC_1_SQRT_2; // ~0.70710678 (Butterworth Q)
        let alpha = sin_w / (2.0 * q);

        let a0 = 1.0 + alpha;
        self.b0 = ((1.0 + cos_w) / 2.0) / a0;
        self.b1 = (-(1.0 + cos_w)) / a0;
        self.b2 = ((1.0 + cos_w) / 2.0) / a0;
        self.a1 = (-2.0 * cos_w) / a0;
        self.a2 = (1.0 - alpha) / a0;
    }

    /// Set coefficients for a Low Shelf Filter
    pub fn set_low_shelf(&mut self, fc: f32, fs: f32, gain_db: f32, q: f32) {
        let a = 10.0f32.powf(gain_db / 40.0);
        let omega = 2.0 * std::f32::consts::PI * fc / fs;
        let cos_w = omega.cos();
        let sin_w = omega.sin();
        let alpha = sin_w / (2.0 * q);
        let two_sqrt_a_alpha = 2.0 * a.sqrt() * alpha;

        let a0 = (a + 1.0) + (a - 1.0) * cos_w + two_sqrt_a_alpha;
        self.b0 = (a * ((a + 1.0) - (a - 1.0) * cos_w + two_sqrt_a_alpha)) / a0;
        self.b1 = (2.0 * a * ((a - 1.0) - (a + 1.0) * cos_w)) / a0;
        self.b2 = (a * ((a + 1.0) - (a - 1.0) * cos_w - two_sqrt_a_alpha)) / a0;
        self.a1 = (-2.0 * ((a - 1.0) + (a + 1.0) * cos_w)) / a0;
        self.a2 = ((a + 1.0) + (a - 1.0) * cos_w - two_sqrt_a_alpha) / a0;
    }

    /// Set coefficients for a High Shelf Filter
    pub fn set_high_shelf(&mut self, fc: f32, fs: f32, gain_db: f32, q: f32) {
        let a = 10.0f32.powf(gain_db / 40.0);
        let omega = 2.0 * std::f32::consts::PI * fc / fs;
        let cos_w = omega.cos();
        let sin_w = omega.sin();
        let alpha = sin_w / (2.0 * q);
        let two_sqrt_a_alpha = 2.0 * a.sqrt() * alpha;

        let a0 = (a + 1.0) - (a - 1.0) * cos_w + two_sqrt_a_alpha;
        self.b0 = (a * ((a + 1.0) + (a - 1.0) * cos_w + two_sqrt_a_alpha)) / a0;
        self.b1 = (-2.0 * a * ((a - 1.0) + (a + 1.0) * cos_w)) / a0;
        self.b2 = (a * ((a + 1.0) + (a - 1.0) * cos_w - two_sqrt_a_alpha)) / a0;
        self.a1 = (2.0 * ((a - 1.0) - (a + 1.0) * cos_w)) / a0;
        self.a2 = ((a + 1.0) - (a - 1.0) * cos_w - two_sqrt_a_alpha) / a0;
    }

    /// Set coefficients for a Peaking/Notch Filter
    pub fn set_peaking_eq(&mut self, fc: f32, fs: f32, gain_db: f32, q: f32) {
        let a = 10.0f32.powf(gain_db / 40.0);
        let omega = 2.0 * std::f32::consts::PI * fc / fs;
        let cos_w = omega.cos();
        let sin_w = omega.sin();
        let alpha = sin_w / (2.0 * q);

        let a0 = 1.0 + alpha / a;
        self.b0 = (1.0 + alpha * a) / a0;
        self.b1 = (-2.0 * cos_w) / a0;
        self.b2 = (1.0 - alpha * a) / a0;
        self.a1 = (-2.0 * cos_w) / a0;
        self.a2 = (1.0 - alpha / a) / a0;
    }
}
