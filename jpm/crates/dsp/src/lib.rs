pub mod biquad;
pub mod oversampler;
pub mod preamp;
pub mod tone_stack;
pub mod poweramp;
pub mod cabinet;
pub mod reverb;

/// The JPM 8000 routing modes.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum RoutingMode {
    /// Post-Cabinet / Studio: Preamp -> Power Amp -> Cabinet -> Reverb
    PostCabinet = 0,
    /// Pre-Power Amp / FX Loop: Preamp -> Reverb -> Power Amp -> Cabinet
    PrePowerAmp = 1,
}

/// The main JPM 8000 DSP Pipeline Runner.
/// Coordinates the signal chain and dynamic parameter routing.
#[derive(Debug, Clone)]
pub struct Jpm8000Engine {
    pub preamp: preamp::Preamp,
    pub tone_stack: tone_stack::ToneStack,
    pub power_amp: poweramp::PowerAmp,
    pub cabinet: cabinet::Cabinet,
    pub reverb: reverb::Reverb,
    routing_mode: RoutingMode,
    fs: f32,
}

impl Jpm8000Engine {
    pub fn new(fs: f32) -> Self {
        Self {
            preamp: preamp::Preamp::new(fs),
            tone_stack: tone_stack::ToneStack::new(fs),
            power_amp: poweramp::PowerAmp::new(fs),
            cabinet: cabinet::Cabinet::new(fs),
            reverb: reverb::Reverb::new(fs),
            routing_mode: RoutingMode::PostCabinet,
            fs,
        }
    }

    /// Reset all internal DSP states to zero
    pub fn reset(&mut self) {
        self.preamp.reset();
        self.tone_stack.reset();
        self.power_amp.reset();
        self.cabinet.reset();
        self.reverb.reset();
    }

    /// Set a new sample rate for the entire DSP engine
    pub fn set_sample_rate(&mut self, fs: f32) {
        self.fs = fs;
        self.preamp.set_sample_rate(fs);
        self.tone_stack.set_sample_rate(fs);
        self.power_amp.set_sample_rate(fs);
        self.cabinet.set_sample_rate(fs);
        self.reverb.set_sample_rate(fs);
    }

    /// Set routing mode for the signal chain
    pub fn set_routing_mode(&mut self, mode: RoutingMode) {
        self.routing_mode = mode;
    }

    /// Return the active routing mode
    pub fn routing_mode(&self) -> RoutingMode {
        self.routing_mode
    }

    /// Process a single mono sample through the selected signal chain pipeline
    #[inline(always)]
    pub fn process_sample(&mut self, x: f32) -> f32 {
        // Preamp and Tone Stack are always the start of the chain
        let x_preamp = self.preamp.process_sample(x);
        let x_eq = self.tone_stack.process_sample(x_preamp);

        match self.routing_mode {
            RoutingMode::PostCabinet => {
                // Mode 0: Preamp -> EQ -> Power Amp -> Cabinet -> Reverb (Clean tails)
                let x_power = self.power_amp.process_sample(x_eq);
                let x_cab = self.cabinet.process_sample(x_power);
                self.reverb.process_sample(x_cab)
            }
            RoutingMode::PrePowerAmp => {
                // Mode 1: Preamp -> EQ -> Reverb -> Power Amp -> Cabinet (Vintage tube distorted tails)
                let x_reverb = self.reverb.process_sample(x_eq);
                let x_power = self.power_amp.process_sample(x_reverb);
                self.cabinet.process_sample(x_power)
            }
        }
    }

    /// Process a block of audio samples in-place.
    /// Perfectly matches our zero heap-allocation criteria.
    pub fn process_buffer(&mut self, buffer: &mut [f32]) {
        match self.routing_mode {
            RoutingMode::PostCabinet => {
                // 1. Preamp -> EQ -> PowerAmp (sample-by-sample)
                for s in buffer.iter_mut() {
                    let x_pre = self.preamp.process_sample(*s);
                    let x_eq = self.tone_stack.process_sample(x_pre);
                    *s = self.power_amp.process_sample(x_eq);
                }
                
                // 2. Cabinet (normalizes the block and applies FIR + breakup + thermal comp)
                self.cabinet.process_buffer(buffer);
                
                // 3. Reverb (sample-by-sample)
                for s in buffer.iter_mut() {
                    *s = self.reverb.process_sample(*s);
                }
            }
            RoutingMode::PrePowerAmp => {
                // 1. Preamp -> EQ -> Reverb
                for s in buffer.iter_mut() {
                    let x_pre = self.preamp.process_sample(*s);
                    let x_eq = self.tone_stack.process_sample(x_pre);
                    *s = self.reverb.process_sample(x_eq);
                }
                
                // 2. Power Amp (PI saturation, EL34 soft clip, sag, reactive impedance)
                for s in buffer.iter_mut() {
                    *s = self.power_amp.process_sample(*s);
                }
                
                // 3. Cabinet (normalizes the block and applies FIR + breakup + thermal comp)
                self.cabinet.process_buffer(buffer);
            }
        }
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_engine_pipeline_and_routing_modes() {
        let mut engine = Jpm8000Engine::new(44100.0);
        
        // Prepare a test buffer
        let mut buffer_mode0 = [0.0f32; 128];
        buffer_mode0[0] = 1.0; // feed an impulse
        
        engine.set_routing_mode(RoutingMode::PostCabinet);
        engine.process_buffer(&mut buffer_mode0);
        
        // Assert that the impulse response is processed and we have a valid signal
        let energy_mode0: f32 = buffer_mode0.iter().map(|&s| s * s).sum();
        assert!(energy_mode0 > 0.001, "Pipeline should process impulse and produce output");

        // Repeat for Mode 1
        let mut engine2 = Jpm8000Engine::new(44100.0);
        let mut buffer_mode1 = [0.0f32; 128];
        buffer_mode1[0] = 1.0;
        
        engine2.set_routing_mode(RoutingMode::PrePowerAmp);
        engine2.process_buffer(&mut buffer_mode1);
        
        let energy_mode1: f32 = buffer_mode1.iter().map(|&s| s * s).sum();
        assert!(energy_mode1 > 0.001);
        
        // Verify that the routing modes produce different outputs due to the order of non-linearities and reverb tails
        let mut diff_sum = 0.0f32;
        for i in 0..128 {
            diff_sum += (buffer_mode0[i] - buffer_mode1[i]).abs();
        }
        assert!(diff_sum > 0.01, "Different routing modes must produce different audio tails, diff={}", diff_sum);
    }
}
