/// JPM 8000 Guitar Amplifier Emulator - Web Audio API AudioWorkletProcessor
/// Manages WASM instantiation, handles zero-copy memory transfers,
/// applies dynamic parameter adjustments, and copies processed mono audio to stereo.
class Jpm8000Processor extends AudioWorkletProcessor {
    constructor() {
        super();
        this.initialized = false;
        this.wasmInstance = null;
        this.enginePtr = 0;
        this.inputPtr = 0;
        this.outputPtr = 0;
        this.transferPtr = 0;
        this.transferLen = 0;

        // Establish message bridge with the UI main thread
        this.port.onmessage = (event) => {
            const msg = event.data;

            if (msg.type === "init") {
                this.initWasm(msg.wasmModule, msg.sampleRate);
            } else if (msg.type === "set_param") {
                if (this.initialized) {
                    this.setParam(msg.id, msg.value);
                }
            } else if (msg.type === "set_routing_mode") {
                if (this.initialized) {
                    this.wasmInstance.exports.jpm8000wasmengine_set_routing_mode(this.enginePtr, msg.mode);
                }
            } else if (msg.type === "load_custom_ir") {
                if (this.initialized) {
                    this.loadCustomIr(msg.channel, msg.samples);
                }
            } else if (msg.type === "reset") {
                if (this.initialized) {
                    this.wasmInstance.exports.jpm8000wasmengine_reset(this.enginePtr);
                }
            }
        };
    }

    /// Instantiate the pre-compiled WebAssembly module inside the AudioWorklet thread.
    async initWasm(wasmModule, sampleRate) {
        try {
            // A master Proxy that intercepts any module lookup and returns a sub-Proxy
            // that intercepts any function lookup and returns a dummy function!
            const imports = new Proxy({
                env: {
                    __wbindgen_throw: (ptr, len) => {
                        throw new Error("WASM Exception in JPM 8000 Core");
                    }
                }
            }, {
                get: (target, prop) => {
                    // If the module exists in our defined target (like 'env'), use it
                    if (prop in target) {
                        return target[prop];
                    }
                    // Otherwise, dynamically return a sub-Proxy that intercepts any function calls and stubs them
                    return new Proxy({}, {
                        get: () => () => 0
                    });
                }
            });

            // Instantiate WASM module directly in worklet
            this.wasmInstance = await WebAssembly.instantiate(wasmModule, imports);
            const exports = this.wasmInstance.exports;

            // Instantiate the Jpm8000WasmEngine in WASM memory
            this.enginePtr = exports.jpm8000wasmengine_new(sampleRate);

            // Cache raw float32 memory heap pointers (zero-copy bridge)
            this.inputPtr = exports.jpm8000wasmengine_input_ptr(this.enginePtr);
            this.outputPtr = exports.jpm8000wasmengine_output_ptr(this.enginePtr);
            this.transferPtr = exports.jpm8000wasmengine_transfer_ptr(this.enginePtr);
            this.transferLen = exports.jpm8000wasmengine_transfer_len(this.enginePtr);

            this.initialized = true;
            this.port.postMessage({ type: "ready" });
        } catch (err) {
            this.port.postMessage({ type: "error", error: err.message });
        }
    }

    /// Set dynamic target parameters for the exponential smoothers (eliminates click artifacts)
    setParam(id, value) {
        const exports = this.wasmInstance.exports;
        switch (id) {
            case "pre_gain": exports.jpm8000wasmengine_set_pre_gain(this.enginePtr, value); break;
            case "bass": exports.jpm8000wasmengine_set_bass(this.enginePtr, value); break;
            case "mid": exports.jpm8000wasmengine_set_mid(this.enginePtr, value); break;
            case "treble": exports.jpm8000wasmengine_set_treble(this.enginePtr, value); break;
            case "master": exports.jpm8000wasmengine_set_master(this.enginePtr, value); break;
            case "resonance": exports.jpm8000wasmengine_set_resonance(this.enginePtr, value); break;
            case "presence": exports.jpm8000wasmengine_set_presence(this.enginePtr, value); break;
            case "mic_blend": exports.jpm8000wasmengine_set_mic_blend(this.enginePtr, value); break;
            case "mic_dist": exports.jpm8000wasmengine_set_mic_dist(this.enginePtr, value); break;
            case "reverb_mix": exports.jpm8000wasmengine_set_reverb_mix(this.enginePtr, value); break;
            case "reverb_room_size": exports.jpm8000wasmengine_set_reverb_room_size(this.enginePtr, value); break;
            case "reverb_damp": exports.jpm8000wasmengine_set_reverb_damp(this.enginePtr, value); break;
        }
    }

    /// Load raw custom impulse response PCM float array into WASM memory via transfer buffer
    loadCustomIr(channel, samples) {
        const exports = this.wasmInstance.exports;
        const memory = exports.memory;

        // Overlay a Float32Array directly over WASM's linear memory space
        const transferView = new Float32Array(memory.buffer, this.transferPtr, this.transferLen);

        // Copy user samples into the transfer view
        const copyLen = Math.min(samples.length, this.transferLen);
        transferView.set(samples.subarray(0, copyLen));

        // Command the engine to load the samples into the cabinet module
        exports.jpm8000wasmengine_load_custom_ir_from_transfer(this.enginePtr, channel, copyLen);
    }

    /// Main audio loop processing block of 128 samples
    process(inputs, outputs, parameters) {
        if (!this.initialized) {
            // Pass through silence if the engine is compiling/loading
            return true;
        }

        const input = inputs[0];
        const output = outputs[0];

        // Access the raw WebAssembly memory buffer
        const memory = this.wasmInstance.exports.memory;

        // Overlay Float32 views directly on WASM pre-allocated heap buffers
        const wasmInput = new Float32Array(memory.buffer, this.inputPtr, 128);
        const wasmOutput = new Float32Array(memory.buffer, this.outputPtr, 128);

        // 1. Copy incoming mono audio into WASM input buffer (zero-copy bridge)
        if (input && input[0]) {
            wasmInput.set(input[0]);
        } else {
            wasmInput.fill(0);
        }

        // 2. Process DSP Pipeline inside WASM (zero allocations, 10 ms parameter smoothing)
        this.wasmInstance.exports.jpm8000wasmengine_process(this.enginePtr);

        // 3. Map Mono WASM output to Stereo Web Audio outputs
        if (output) {
            if (output[0]) {
                output[0].set(wasmOutput); // Left Channel
            }
            if (output[1]) {
                output[1].set(wasmOutput); // Right Channel
            }
        }

        return true;
    }
}

registerProcessor("jpm8000-processor", Jpm8000Processor);
