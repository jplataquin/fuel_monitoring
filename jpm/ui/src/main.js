/// JPM 8000 Guitar Emulator - Web UI Main Control & Web Audio API Bridge
/// Coordinates WASM compilation, AudioWorklet initialization, knob dragging physics,
/// custom impulse response decoding, and a procedurally-strummed electric guitar riff.

let audioContext = null;
let workletNode = null;
let isPowerOn = false;
let isPlaying = false;
let synthIntervalId = null;

// Microphone / Live input variables
let micStream = null;
let micSourceNode = null;
let isLiveInputActive = false;

// Default JPM 8000 parameter settings
const params = {
    pre_gain: 4.5, // Crunchy drive
    bass: 0.6,
    mid: 0.45,     // Scooped mid scoop
    treble: 0.65,
    master: 1.2,   // Saturated power tubes
    presence: 0.6,
    resonance: 0.5,
    reverb_mix: 0.25,
    reverb_room_size: 0.75,
    reverb_damp: 0.2,
};

// Start application when DOM is ready
window.addEventListener("DOMContentLoaded", () => {
    initKnobControls();
    initSwitches();
    initDragAndDrop();
    initCustomAudioUpload();
});

// ==========================================================================
// KNOB CONTROL INTERACTIONS (Vertical Drag)
// ==========================================================================
function initKnobControls() {
    const knobWrappers = document.querySelectorAll(".knob-wrapper");

    knobWrappers.forEach((wrapper) => {
        const paramId = wrapper.getAttribute("data-param");
        const min = parseFloat(wrapper.getAttribute("data-min"));
        const max = parseFloat(wrapper.getAttribute("data-max"));
        const initialVal = params[paramId] !== undefined ? params[paramId] : parseFloat(wrapper.getAttribute("data-value"));
        
        const knob = wrapper.querySelector(".knob");
        const valueDisplay = wrapper.querySelector(".value-display");

        // Sync local parameter state
        params[paramId] = initialVal;

        // Set initial knob rotation and visual label
        updateKnobVisual(knob, valueDisplay, initialVal, min, max, paramId);

        // Vertical dragging logic
        let startY = 0;
        let startVal = 0;

        const onMouseMove = (e) => {
            const deltaY = startY - e.clientY;
            // 150px vertical drag spans full range
            const range = max - min;
            const deltaVal = (deltaY / 150) * range;
            let newVal = startVal + deltaVal;
            newVal = Math.max(min, Math.min(max, newVal));

            params[paramId] = newVal;
            updateKnobVisual(knob, valueDisplay, newVal, min, max, paramId);
            sendParamToWasm(paramId, newVal);
        };

        const onMouseUp = () => {
            document.removeEventListener("mousemove", onMouseMove);
            document.removeEventListener("mouseup", onMouseUp);
        };

        knob.addEventListener("mousedown", (e) => {
            startY = e.clientY;
            startVal = params[paramId];
            document.addEventListener("mousemove", onMouseMove);
            document.addEventListener("mouseup", onMouseUp);
            e.preventDefault();
        });

        // Mobile touch support
        const onTouchMove = (e) => {
            const deltaY = startY - e.touches[0].clientY;
            const range = max - min;
            const deltaVal = (deltaY / 150) * range;
            let newVal = startVal + deltaVal;
            newVal = Math.max(min, Math.min(max, newVal));

            params[paramId] = newVal;
            updateKnobVisual(knob, valueDisplay, newVal, min, max, paramId);
            sendParamToWasm(paramId, newVal);
        };

        const onTouchEnd = () => {
            document.removeEventListener("touchmove", onTouchMove);
            document.removeEventListener("touchend", onTouchEnd);
        };

        knob.addEventListener("touchstart", (e) => {
            startY = e.touches[0].clientY;
            startVal = params[paramId];
            document.addEventListener("touchmove", onTouchMove, { passive: false });
            document.addEventListener("touchend", onTouchEnd);
            e.preventDefault();
        });
    });
}

function updateKnobVisual(knob, display, value, min, max, paramId) {
    // Standard visual mapping: -135deg (min) to +135deg (max)
    const ratio = (value - min) / (max - min);
    const deg = -135 + ratio * 270;
    knob.style.transform = `rotate(${deg}deg)`;

    // Format value string for display
    let dispVal = value;
    if (paramId === "reverb_mix") {
        dispVal = Math.round(value * 100) + "%";
    } else if (["pre_gain", "bass", "mid", "treble", "master", "presence", "resonance", "reverb_room_size", "reverb_damp"].includes(paramId)) {
        // Map [0.0, 1.0] to classic [0, 10] faceplate labels
        const scaled = (paramId === "pre_gain") ? value : (paramId === "master") ? (value * 5.0) : (value * 10.0);
        dispVal = scaled.toFixed(1);
    }
    display.textContent = dispVal;
}

// ==========================================================================
// POWER SWITCHES & INPUT JACKS
// ==========================================================================
function initSwitches() {
    const powerSwitch = document.getElementById("power-switch");
    const jewelLamp = document.getElementById("jewel-lamp");
    const routingSwitch = document.getElementById("routing-switch");
    const sourceSwitch = document.getElementById("source-switch");
    const inputJack = document.getElementById("jack-click");

    powerSwitch.addEventListener("change", async (e) => {
        isPowerOn = e.target.checked;
        if (isPowerOn) {
            jewelLamp.classList.add("active");
            await initAudio();
        } else {
            jewelLamp.classList.remove("active");
            stopGuitarLoop();
            disableLiveInput();
            sourceSwitch.checked = false;
            if (audioContext) {
                audioContext.suspend();
            }
        }
    });

    routingSwitch.addEventListener("change", (e) => {
        const mode = e.target.checked ? 1 : 0; // 0 = PostCab, 1 = PrePower (FX Loop)
        if (workletNode) {
            workletNode.port.postMessage({ type: "set_routing_mode", mode });
        }
    });

    sourceSwitch.addEventListener("change", async (e) => {
        const useLive = e.target.checked;
        if (!isPowerOn) {
            alert("Turn on the POWER switch first to boot the 12AX7 vacuum tubes!");
            e.target.checked = false;
            return;
        }

        if (useLive) {
            // Enable live mic/interface stream
            stopGuitarLoop();
            await enableLiveInput();
        } else {
            // Disable live mic/interface stream
            disableLiveInput();
        }
    });

    inputJack.addEventListener("click", () => {
        if (!isPowerOn) {
            alert("Turn on the POWER switch first to boot the 12AX7 vacuum tubes!");
            return;
        }
        if (isLiveInputActive) {
            alert("Turn off the LIVE source switch first to play the built-in arpeggiator loop!");
            return;
        }
        toggleGuitarLoop();
    });
}

// ==========================================================================
// WEB AUDIO & WASM COMPILATION ENGINE BRIDGE
// ==========================================================================
async function initAudio() {
    if (audioContext) {
        audioContext.resume();
        return;
    }

    try {
        audioContext = new (window.AudioContext || window.webkitAudioContext)({ latencyHint: "interactive" });
        
        // Fetch and compile raw WASM binary from the server (with cache-busting)
        const response = await fetch("public/dsp_wasm_bg.wasm?v=" + Date.now());
        if (!response.ok) {
            throw new Error(`Failed to download JPM 8000 WASM binary: ${response.statusText}`);
        }
        const wasmBytes = await response.arrayBuffer();
        
        // Compile to WebAssembly.Module (allows secure transfer to AudioWorklet thread)
        const wasmModule = await WebAssembly.compile(wasmBytes);

        // Load the AudioWorklet processor script (with cache-busting)
        await audioContext.audioWorklet.addModule("public/audio-processor.js?v=" + Date.now());

        // Instantiate Worklet Node
        workletNode = new AudioWorkletNode(audioContext, "jpm8000-processor");
        
        // Initialize WASM inside the Worklet thread
        workletNode.port.postMessage({
            type: "init",
            wasmModule,
            sampleRate: audioContext.sampleRate
        });

        // Set up feedback receiver
        workletNode.port.onmessage = (e) => {
            if (e.data.type === "ready") {
                console.log("🚀 JPM 8000 WASM DSP Engine fully active!");
                // Flush all initial parameter targets to the smoother
                for (const [id, val] of Object.entries(params)) {
                    sendParamToWasm(id, val);
                }
                const routeMode = document.getElementById("routing-switch").checked ? 1 : 0;
                workletNode.port.postMessage({ type: "set_routing_mode", mode: routeMode });
            } else if (e.data.type === "error") {
                console.error("WASM Bridge Error: ", e.data.error);
            }
        };

        // Connect DSP Worklet node directly to Left & Right speakers
        workletNode.connect(audioContext.destination);

    } catch (err) {
        console.error("Critical Web Audio Initialization failure: ", err);
        alert(`Failed to boot JPM 8000: ${err.message}`);
    }
}

function sendParamToWasm(id, value) {
    if (workletNode) {
        workletNode.port.postMessage({ type: "set_param", id, value });
    }
}

// ==========================================================================
// DRAG & DROP CUSTOM IMPULSE RESPONSES
// ==========================================================================
function initDragAndDrop() {
    const dropZone = document.getElementById("drop-zone");
    const micBlendSlider = document.getElementById("mic-blend-slider");
    const micBlendVal = document.getElementById("mic-blend-val");
    const micDistSlider = document.getElementById("mic-dist-slider");
    const micDistVal = document.getElementById("mic-dist-val");

    // Standard slider visuals
    micBlendSlider.addEventListener("input", (e) => {
        const val = parseFloat(e.target.value);
        micBlendVal.textContent = `${Math.round((1 - val) * 100)}% SM57 / ${Math.round(val * 100)}% R-121`;
        sendParamToWasm("mic_blend", val);
    });

    micDistSlider.addEventListener("input", (e) => {
        const val = parseFloat(e.target.value);
        micDistVal.textContent = `${val.toFixed(2)}m`;
        sendParamToWasm("mic_dist", val);
    });

    // Drag-and-Drop dragover handlers
    dropZone.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropZone.classList.add("dragover");
    });

    dropZone.addEventListener("dragleave", () => {
        dropZone.classList.remove("dragover");
    });

    dropZone.addEventListener("drop", async (e) => {
        e.preventDefault();
        dropZone.classList.remove("dragover");

        if (!isPowerOn) {
            alert("Power up the amp before installing custom speaker cones!");
            return;
        }

        const file = e.dataTransfer.files[0];
        if (file) {
            await handleIrFile(file);
        }
    });

    dropZone.addEventListener("click", () => {
        if (!isPowerOn) {
            alert("Power up the amp before installing custom speaker cones!");
            return;
        }
        
        // Open file picker
        const input = document.createElement("input");
        input.type = "file";
        input.accept = "audio/wav,audio/x-wav";
        input.onchange = async (e) => {
            const file = e.target.files[0];
            if (file) {
                await handleIrFile(file);
            }
        };
        input.click();
    });
}

async function handleIrFile(file) {
    try {
        const reader = new FileReader();
        reader.onload = async (e) => {
            const arrayBuffer = e.target.result;
            // Decode WAV data in the browser
            const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);
            
            // Get raw float channel data (mono convolution, so take channel 0)
            const rawSamples = audioBuffer.getChannelData(0);
            
            // Send custom samples to both Channel A and Channel B (swaps both defaults)
            if (workletNode) {
                workletNode.port.postMessage({
                    type: "load_custom_ir",
                    channel: 0,
                    samples: rawSamples
                });
                workletNode.port.postMessage({
                    type: "load_custom_ir",
                    channel: 1,
                    samples: rawSamples
                });
                
                document.querySelector(".drop-zone-text").textContent = `CONE SWAPPED: ${file.name.toUpperCase()}`;
                document.querySelector(".drop-zone-sub").textContent = `Linear 512-tap zero-latency FIR successfully loaded.`;
                document.getElementById("mic-blend-val").textContent = "CUSTOM IR CONVOLVER ACTIVE";
            }
        };
        reader.readAsArrayBuffer(file);
    } catch (err) {
        alert(`Failed to parse custom IR .wav file: ${err.message}`);
    }
}

// ==========================================================================
// PROCEDURAL ELECTRIC GUITAR PLUCK SYNTHESIZER
// ==========================================================================
function toggleGuitarLoop() {
    const jack = document.getElementById("jack-click");
    if (isPlaying) {
        stopGuitarLoop();
        jack.classList.remove("playing");
    } else {
        startGuitarLoop();
        jack.classList.add("playing");
    }
}

function startGuitarLoop() {
    isPlaying = true;

    // Classic heavy rock chord progression in E/A minor:
    // Am (x02210) -> G (320033) -> F (133211) -> E (022100)
    const Am = [110.00, 164.81, 220.00, 261.63, 329.63]; // A2, E3, A3, C4, E4
    const G  = [98.00,  146.83, 196.00, 246.94, 293.66]; // G2, D3, G3, B3, D4
    const F  = [87.31,  130.81, 174.61, 220.00, 261.63]; // F2, C3, F3, A3, C4
    const E  = [82.41,  123.47, 164.81, 207.65, 246.94]; // E2, B2, E3, G#3, B3
    
    const chords = [Am, G, F, E];
    let chordIdx = 0;

    const strumChord = () => {
        const chord = chords[chordIdx];
        const now = audioContext.currentTime;

        // Clean arpeggio "strumming" effect sweep across 5 strings (staggered by 30 ms)
        for (let i = 0; i < chord.length; i++) {
            pluckString(chord[i], now + i * 0.035);
        }

        chordIdx = (chordIdx + 1) % chords.length;
    };

    // Strum a new chord every 2 seconds
    strumChord();
    synthIntervalId = setInterval(strumChord, 2000);
}

function stopGuitarLoop() {
    isPlaying = false;
    if (synthIntervalId) {
        clearInterval(synthIntervalId);
        synthIntervalId = null;
    }
}

/// A physical electric guitar plucking simulator using decaying Web Audio Oscillators
function pluckString(freq, startTime) {
    if (!audioContext || !workletNode) return;

    // We combine a Sawtooth (bright pick string snap) and Triangle (full body core)
    const osc1 = audioContext.createOscillator();
    const osc2 = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    osc1.type = "sawtooth";
    osc1.frequency.setValueAtTime(freq, startTime);
    // Subtle detune to represent acoustic string chorus vibration
    osc1.detune.setValueAtTime(-5, startTime);

    osc2.type = "triangle";
    osc2.frequency.setValueAtTime(freq, startTime);
    osc2.detune.setValueAtTime(5, startTime);

    // Dynamic guitar amplitude envelope: instant pick attack (5 ms) -> decaying body string tail
    gainNode.gain.setValueAtTime(0.0, startTime);
    gainNode.gain.linearRampToValueAtTime(0.18, startTime + 0.005); // Snap peak volume
    gainNode.gain.exponentialRampToValueAtTime(0.0001, startTime + 1.8); // 1.8s ring decay

    // Connect oscillators to the envelope gain node
    osc1.connect(gainNode);
    osc2.connect(gainNode);

    // Bridge directly into the high-gain JPM 8000 WASM AudioWorklet input!
    gainNode.connect(workletNode);

    // Start plucking and schedule cleanup
    osc1.start(startTime);
    osc2.start(startTime);
    osc1.stop(startTime + 1.9);
    osc2.stop(startTime + 1.9);
}

// ==========================================================================
// CUSTOM AUDIO FILE UPLOADER
// ==========================================================================
function initCustomAudioUpload() {
    const uploader = document.getElementById("audio-upload");
    let audioSource = null;

    uploader.addEventListener("change", async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (!isPowerOn) {
            alert("Boot up the JPM 8000 before plugging in your custom guitar tracks!");
            return;
        }

        stopGuitarLoop();
        document.getElementById("jack-click").classList.remove("playing");

        try {
            const arrayBuffer = await file.arrayBuffer();
            const decodedBuffer = await audioContext.decodeAudioData(arrayBuffer);

            if (audioSource) {
                audioSource.stop();
            }

            // Create a looping audio source node for your track
            audioSource = audioContext.createBufferSource();
            audioSource.buffer = decodedBuffer;
            audioSource.loop = true;

            // Connect track straight to JPM 8000 core
            audioSource.connect(workletNode);
            audioSource.start(0);

            console.log(`🔊 Looping custom track "${file.name}" through tubes!`);
        } catch (err) {
            alert(`Failed to load custom audio: ${err.message}`);
        }
    });
}

// ==========================================================================
// MICROPHONE / LIVE AUDIO INTERFACE STREAMING
// ==========================================================================
async function enableLiveInput() {
    if (!audioContext || !workletNode) return;

    try {
        // Disabling browser filtering for maximum high-gain guitar fidelity!
        micStream = await navigator.mediaDevices.getUserMedia({
            audio: {
                echoCancellation: false,
                noiseSuppression: false,
                autoGainControl: false,
                latency: 0
            }
        });

        micSourceNode = audioContext.createMediaStreamSource(micStream);
        micSourceNode.connect(workletNode);
        isLiveInputActive = true;

        document.getElementById("jack-click").classList.add("playing");
        document.querySelector(".drop-zone-text").textContent = "LIVE DEVICE INPUT ACTIVE";
        document.querySelector(".drop-zone-sub").textContent = "Plug your guitar or mic in and turn up the Pre-Gain!";
        console.log("🎤 Live device input stream successfully established and connected!");
    } catch (err) {
        console.error("Failed to acquire device audio stream: ", err);
        alert(`Failed to access device microphone: ${err.message}. Check browser permissions!`);
        document.getElementById("source-switch").checked = false;
    }
}

function disableLiveInput() {
    isLiveInputActive = false;
    document.getElementById("jack-click").classList.remove("playing");
    document.querySelector(".drop-zone-text").textContent = "DRAG & DROP CUSTOM .WAV IR HERE";
    document.querySelector(".drop-zone-sub").textContent = "Default SM57 / Royer R-121 models automatically loaded";

    if (micSourceNode) {
        micSourceNode.disconnect();
        micSourceNode = null;
    }

    if (micStream) {
        micStream.getTracks().forEach(track => track.stop());
        micStream = null;
    }
}

