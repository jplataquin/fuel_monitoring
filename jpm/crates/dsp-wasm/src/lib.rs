use wasm_bindgen::prelude::*;
use dsp::{Jpm8000Engine, RoutingMode};

/// A helper structure to apply exponential smoothing to audio parameters.
/// Eliminates "zippering" noise and pops during real-time sweeps.
#[derive(Debug, Clone, Copy)]
pub struct SmoothedParam {
    current: f32,
    target: f32,
}

impl SmoothedParam {
    pub fn new(val: f32) -> Self {
        Self {
            current: val,
            target: val,
        }
    }

    #[inline(always)]
    pub fn set_target(&mut self, target: f32) {
        self.target = target;
    }

    #[inline(always)]
    pub fn step(&mut self, alpha: f32) -> f32 {
        self.current += (self.target - self.current) * alpha;
        self.current
    }
}

/// The main WebAssembly-exported interface for the JPM 8000 DSP engine.
/// Owns the pure-Rust pipeline, pre-allocated static input/output buffers,
/// and handles real-time parameter smoothing.
#[wasm_bindgen]
pub struct Jpm8000WasmEngine {
    engine: Jpm8000Engine,
    input_buf: [f32; 128],
    output_buf: [f32; 128],
    transfer_buf: [f32; 512], // Static transfer buffer for uploading custom impulse responses
    
    // Parameter smoothers
    pre_gain: SmoothedParam,
    bass: SmoothedParam,
    mid: SmoothedParam,
    treble: SmoothedParam,
    master: SmoothedParam,
    resonance: SmoothedParam,
    presence: SmoothedParam,
    mic_blend: SmoothedParam,
    mic_dist: SmoothedParam,
    reverb_mix: SmoothedParam,
    reverb_room_size: SmoothedParam,
    reverb_damp: SmoothedParam,
    
    alpha: f32, // Smoothing coefficient (10 ms time constant)
}

#[wasm_bindgen]
impl Jpm8000WasmEngine {
    /// Instantiate a new WASM-wrapped engine at the given sample rate
    #[wasm_bindgen(constructor)]
    pub fn new(fs: f32) -> Self {
        // Calculate 10 ms exponential smoothing coefficient: alpha = 1.0 - exp(-1.0 / (fs * 0.010))
        let alpha = 1.0 - (-1.0 / (fs * 0.010)).exp();

        Self {
            engine: Jpm8000Engine::new(fs),
            input_buf: [0.0; 128],
            output_buf: [0.0; 128],
            transfer_buf: [0.0; 512],

            pre_gain: SmoothedParam::new(1.0),
            bass: SmoothedParam::new(0.5),
            mid: SmoothedParam::new(0.5),
            treble: SmoothedParam::new(0.5),
            master: SmoothedParam::new(1.0),
            resonance: SmoothedParam::new(0.5),
            presence: SmoothedParam::new(0.5),
            mic_blend: SmoothedParam::new(0.5),
            mic_dist: SmoothedParam::new(0.2),
            reverb_mix: SmoothedParam::new(0.25),
            reverb_room_size: SmoothedParam::new(0.7),
            reverb_damp: SmoothedParam::new(0.2),

            alpha,
        }
    }

    /// Reset internal state
    pub fn reset(&mut self) {
        self.engine.reset();
        self.input_buf = [0.0; 128];
        self.output_buf = [0.0; 128];
        self.transfer_buf = [0.0; 512];
    }

    /// Set a new sample rate and recalculate coefficient
    pub fn set_sample_rate(&mut self, fs: f32) {
        self.engine.set_sample_rate(fs);
        self.alpha = 1.0 - (-1.0 / (fs * 0.010)).exp();
    }

    /// Expose direct WASM heap memory pointer for the input buffer (zero-copy)
    pub fn input_ptr(&self) -> *const f32 {
        self.input_buf.as_ptr()
    }

    /// Expose direct WASM heap memory pointer for the output buffer (zero-copy)
    pub fn output_ptr(&self) -> *const f32 {
        self.output_buf.as_ptr()
    }

    /// Expose direct WASM heap memory pointer for the static IR transfer buffer (zero-copy)
    pub fn transfer_ptr(&self) -> *const f32 {
        self.transfer_buf.as_ptr()
    }

    /// Expose the size of the static IR transfer buffer
    pub fn transfer_len(&self) -> usize {
        512
    }

    /// Load custom IR into Channel A (0) or Channel B (1) from the static transfer buffer
    pub fn load_custom_ir_from_transfer(&mut self, channel: usize, len: usize) {
        let copy_len = len.min(512);
        self.engine.cabinet.load_custom_ir(channel, &self.transfer_buf[..copy_len]);
    }

    // --- Real-time Parameter Setters (Updates Targets for the Smoothers) ---

    pub fn set_pre_gain(&mut self, val: f32) { self.pre_gain.set_target(val); }
    pub fn set_bass(&mut self, val: f32) { self.bass.set_target(val); }
    pub fn set_mid(&mut self, val: f32) { self.mid.set_target(val); }
    pub fn set_treble(&mut self, val: f32) { self.treble.set_target(val); }
    pub fn set_master(&mut self, val: f32) { self.master.set_target(val); }
    pub fn set_resonance(&mut self, val: f32) { self.resonance.set_target(val); }
    pub fn set_presence(&mut self, val: f32) { self.presence.set_target(val); }
    pub fn set_mic_blend(&mut self, val: f32) { self.mic_blend.set_target(val); }
    pub fn set_mic_dist(&mut self, val: f32) { self.mic_dist.set_target(val); }
    pub fn set_reverb_mix(&mut self, val: f32) { self.reverb_mix.set_target(val); }
    pub fn set_reverb_room_size(&mut self, val: f32) { self.reverb_room_size.set_target(val); }
    pub fn set_reverb_damp(&mut self, val: f32) { self.reverb_damp.set_target(val); }
    
    pub fn set_routing_mode(&mut self, mode: u32) {
        let route = if mode == 1 { RoutingMode::PrePowerAmp } else { RoutingMode::PostCabinet };
        self.engine.set_routing_mode(route);
    }

    /// Load a custom impulse response directly from JS-allocated WASM heap
    pub fn load_custom_ir(&mut self, channel: usize, ptr: *const f32, len: usize) {
        if ptr.is_null() || len == 0 {
            return;
        }
        // Safe pointer-to-slice casting representing zero-copy memory bridge
        let samples = unsafe { std::slice::from_raw_parts(ptr, len) };
        self.engine.cabinet.load_custom_ir(channel, samples);
    }

    /// Process the current block of 128 samples inside the pre-allocated buffers.
    /// Incorporates dynamic 10 ms parameter smoothing on every single sample step.
    pub fn process(&mut self) {
        let mode = self.engine.routing_mode();

        match mode {
            RoutingMode::PostCabinet => {
                // 1. Preamp -> EQ -> PowerAmp (sample-by-sample with smoothing)
                for i in 0..128 {
                    let pg = self.pre_gain.step(self.alpha);
                    let b = self.bass.step(self.alpha);
                    let m = self.mid.step(self.alpha);
                    let t = self.treble.step(self.alpha);
                    let ma = self.master.step(self.alpha);
                    let res = self.resonance.step(self.alpha);
                    let pres = self.presence.step(self.alpha);

                    self.engine.preamp.set_pre_gain(pg);
                    self.engine.tone_stack.set_params(b, m, t);
                    self.engine.power_amp.set_params(ma, res, pres);

                    let x = self.input_buf[i];
                    let x_pre = self.engine.preamp.process_sample(x);
                    let x_eq = self.engine.tone_stack.process_sample(x_pre);
                    self.output_buf[i] = self.engine.power_amp.process_sample(x_eq);
                }

                // 2. Cabinet (block processing with head/cone headroom normalization)
                let mb = self.mic_blend.step(self.alpha);
                let md = self.mic_dist.step(self.alpha);
                self.engine.cabinet.set_params(mb, md);
                self.engine.cabinet.process_buffer(&mut self.output_buf);

                // 3. Reverb (sample-by-sample with smoothing)
                for i in 0..128 {
                    let r_mix = self.reverb_mix.step(self.alpha);
                    let r_room = self.reverb_room_size.step(self.alpha);
                    let r_damp = self.reverb_damp.step(self.alpha);

                    self.engine.reverb.set_params(r_mix, r_room, r_damp);
                    self.output_buf[i] = self.engine.reverb.process_sample(self.output_buf[i]);
                }
            }
            RoutingMode::PrePowerAmp => {
                // 1. Preamp -> EQ -> Reverb (sample-by-sample with smoothing)
                for i in 0..128 {
                    let pg = self.pre_gain.step(self.alpha);
                    let b = self.bass.step(self.alpha);
                    let m = self.mid.step(self.alpha);
                    let t = self.treble.step(self.alpha);
                    let r_mix = self.reverb_mix.step(self.alpha);
                    let r_room = self.reverb_room_size.step(self.alpha);
                    let r_damp = self.reverb_damp.step(self.alpha);

                    self.engine.preamp.set_pre_gain(pg);
                    self.engine.tone_stack.set_params(b, m, t);
                    self.engine.reverb.set_params(r_mix, r_room, r_damp);

                    let x = self.input_buf[i];
                    let x_pre = self.engine.preamp.process_sample(x);
                    let x_eq = self.engine.tone_stack.process_sample(x_pre);
                    self.output_buf[i] = self.engine.reverb.process_sample(x_eq);
                }

                // 2. Power Amp (sample-by-sample with smoothing)
                for i in 0..128 {
                    let ma = self.master.step(self.alpha);
                    let res = self.resonance.step(self.alpha);
                    let pres = self.presence.step(self.alpha);

                    self.engine.power_amp.set_params(ma, res, pres);
                    self.output_buf[i] = self.engine.power_amp.process_sample(self.output_buf[i]);
                }

                // 3. Cabinet (block processing with head/cone headroom normalization)
                let mb = self.mic_blend.step(self.alpha);
                let md = self.mic_dist.step(self.alpha);
                self.engine.cabinet.set_params(mb, md);
                self.engine.cabinet.process_buffer(&mut self.output_buf);
            }
        }
    }
}
