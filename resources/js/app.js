import 'bootstrap';

const formatSavedAt = (value) => {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? '' : date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

const initializeAttemptPage = () => {
    const page = document.querySelector('[data-attempt-page]');
    if (!page) {
        return;
    }

    const form = document.querySelector('[data-attempt-form]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const timer = page.querySelector('[data-attempt-timer]');
    const expiresAt = Number(page.dataset.expiresAt || 0) * 1000;
    const serverNow = Number(page.dataset.serverNow || 0) * 1000;
    const clientAtLoad = Date.now();
    let deadlineSubmitted = false;

    const setStatus = (group, message, className = 'text-body-secondary') => {
        const status = group.querySelector('[data-answer-status]');
        if (status) {
            status.textContent = message;
            status.className = `small mt-2 ${className}`;
        }
    };

    const disableAnswers = () => {
        page.querySelectorAll('[data-answer-input]').forEach((input) => {
            input.disabled = true;
        });
    };

    const submitForDeadline = () => {
        if (!form || deadlineSubmitted) {
            return;
        }

        deadlineSubmitted = true;
        disableAnswers();
        if (timer) {
            timer.textContent = 'Time expired — submitting…';
            timer.classList.remove('text-bg-warning');
            timer.classList.add('text-bg-danger');
        }
        form.requestSubmit();
    };

    const updateTimer = () => {
        if (!timer || !expiresAt || !serverNow) {
            return;
        }

        const adjustedNow = serverNow + (Date.now() - clientAtLoad);
        const remaining = Math.max(0, expiresAt - adjustedNow);
        if (remaining <= 0) {
            submitForDeadline();
            return;
        }

        const totalSeconds = Math.ceil(remaining / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        timer.textContent = `${minutes}:${String(seconds).padStart(2, '0')} remaining`;
    };

    if (timer) {
        updateTimer();
        window.setInterval(updateTimer, 1000);
    }

    page.querySelectorAll('[data-answer-input]').forEach((input) => {
        input.addEventListener('change', async () => {
            const group = input.closest('[data-answer-group]');
            if (!group || group.dataset.saving === 'true') {
                return;
            }

            group.dataset.saving = 'true';
            group.querySelectorAll('[data-answer-input]').forEach((answerInput) => {
                answerInput.disabled = true;
            });
            setStatus(group, 'Saving…', 'text-primary');

            try {
                const response = await fetch(group.dataset.saveUrl, {
                    method: 'PUT',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        response: input.value,
                        save_version: Number(group.dataset.saveVersion || 0),
                    }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 409 && payload.deadline_reached) {
                        setStatus(group, 'Deadline reached. Your answer was not saved.', 'text-danger');
                        submitForDeadline();
                    } else if (response.status === 409) {
                        setStatus(group, 'Save conflict. Reload this attempt before changing it.', 'text-danger');
                    } else {
                        setStatus(group, 'Not saved — please try again.', 'text-danger');
                    }
                    return;
                }

                group.dataset.saveVersion = payload.save_version;
                setStatus(group, `Saved at ${formatSavedAt(payload.saved_at)}`, 'text-success');
            } catch (error) {
                setStatus(group, 'Not saved — please try again.', 'text-danger');
            } finally {
                group.dataset.saving = 'false';
                if (!deadlineSubmitted) {
                    group.querySelectorAll('[data-answer-input]').forEach((answerInput) => {
                        answerInput.disabled = false;
                    });
                }
            }
        });
    });

    if (form) {
        form.addEventListener('submit', (event) => {
            if (!deadlineSubmitted && !window.confirm('Submit this practice attempt now?')) {
                event.preventDefault();
            }
        });
    }
};

const initializeWritingEditor = () => {
    document.querySelectorAll('[data-writing-editor]').forEach((editor) => {
        const textarea = editor.querySelector('[data-writing-textarea]');
        const counter = editor.querySelector('[data-writing-word-count]');
        if (!textarea || !counter) {
            return;
        }

        const updateCount = () => {
            const words = textarea.value.trim().match(/\S+/gu) || [];
            counter.textContent = `${words.length} words`;
        };

        textarea.addEventListener('input', updateCount);
        updateCount();
    });
};

const initializeSpeakingRecorder = () => {
    document.querySelectorAll('[data-speaking-recorder]').forEach((recorder) => {
        const form = recorder.closest('[data-speaking-form]');
        const start = recorder.querySelector('[data-speaking-start]');
        const stop = recorder.querySelector('[data-speaking-stop]');
        const play = recorder.querySelector('[data-speaking-play]');
        const download = recorder.querySelector('[data-speaking-download]');
        const status = recorder.querySelector('[data-speaking-status]');
        const timer = recorder.querySelector('[data-speaking-timer]');
        const audio = recorder.querySelector('[data-speaking-audio]');
        const durationInput = recorder.querySelector('[data-speaking-duration]');
        const preparationSeconds = Math.max(0, Number(recorder.dataset.preparationSeconds || 0));
        const speakingSeconds = Math.max(1, Number(recorder.dataset.speakingSeconds || 1));
        const supported = window.isSecureContext && navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function' && typeof window.MediaRecorder === 'function';
        let stream = null;
        let mediaRecorder = null;
        let chunks = [];
        let objectUrl = null;
        let timerId = null;
        let startedAt = 0;
        let phase = 'idle';

        const setStatus = (message, className = 'text-body-secondary') => {
            if (status) {
                status.textContent = message;
                status.className = `small mt-2 ${className}`;
            }
        };

        const formatTime = (seconds) => {
            const minutes = Math.floor(seconds / 60);
            return `${minutes}:${String(seconds % 60).padStart(2, '0')}`;
        };

        const clearTimer = () => {
            if (timerId) {
                window.clearInterval(timerId);
                timerId = null;
            }
        };

        const releaseStream = () => {
            stream?.getTracks().forEach((track) => track.stop());
            stream = null;
        };

        const showReady = () => {
            phase = 'idle';
            if (start) start.disabled = !supported;
            if (stop) stop.disabled = true;
            if (play) play.disabled = !audio?.src;
            setStatus(supported ? 'Ready. The recording stays in this browser and is never uploaded.' : 'Local recording needs HTTPS, microphone permission, and a supported browser. You can still save notes and use the timer.', supported ? 'text-body-secondary' : 'text-warning');
        };

        const finishBlob = () => {
            const blob = new Blob(chunks, { type: mediaRecorder?.mimeType || 'audio/webm' });
            if (objectUrl) window.URL.revokeObjectURL(objectUrl);
            objectUrl = window.URL.createObjectURL(blob);
            if (audio) {
                audio.src = objectUrl;
                audio.hidden = false;
            }
            if (download) {
                download.href = objectUrl;
                download.download = 'speaking-practice.webm';
                download.hidden = false;
            }
            if (durationInput) durationInput.value = String(Math.max(1, Math.round((Date.now() - startedAt) / 1000)));
            releaseStream();
            clearTimer();
            phase = 'ready';
            if (start) start.disabled = !supported;
            if (stop) stop.disabled = true;
            if (play) play.disabled = false;
            setStatus('Recording ready for playback or download. Submit only the duration, notes, and self-review metadata.', 'text-success');
        };

        const stopRecording = () => {
            clearTimer();
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                return;
            }
            releaseStream();
            showReady();
        };

        const beginSpeaking = () => {
            if (!stream || !window.MediaRecorder) return;
            try {
                const mimeType = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4']
                    .find((candidate) => typeof window.MediaRecorder.isTypeSupported !== 'function' || window.MediaRecorder.isTypeSupported(candidate));
                mediaRecorder = new window.MediaRecorder(stream, mimeType ? { mimeType } : undefined);
            } catch (error) {
                releaseStream();
                showReady();
                setStatus('This browser could not create a local recorder. Notes and timer remain available.', 'text-warning');
                return;
            }
            chunks = [];
            mediaRecorder.addEventListener('dataavailable', (event) => {
                if (event.data?.size) chunks.push(event.data);
            });
            mediaRecorder.addEventListener('stop', finishBlob, { once: true });
            mediaRecorder.start();
            startedAt = Date.now();
            phase = 'recording';
            if (start) start.disabled = true;
            if (stop) stop.disabled = false;
            setStatus('Speaking time is running. Your audio is held in this browser only.', 'text-primary');
            let remaining = speakingSeconds;
            if (timer) timer.textContent = `Speaking ${formatTime(remaining)}`;
            timerId = window.setInterval(() => {
                remaining -= 1;
                if (timer) timer.textContent = `Speaking ${formatTime(Math.max(0, remaining))}`;
                if (remaining <= 0) stopRecording();
            }, 1000);
        };

        const startRecording = async () => {
            if (!supported || (phase !== 'idle' && phase !== 'ready')) return;
            if (objectUrl) {
                window.URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
            if (audio) {
                audio.removeAttribute('src');
                audio.hidden = true;
            }
            if (download) download.hidden = true;
            if (durationInput) durationInput.value = '';
            try {
                setStatus('Requesting microphone permission…', 'text-primary');
                stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                phase = 'preparing';
                if (start) start.disabled = true;
                if (stop) stop.disabled = false;
                let remaining = preparationSeconds;
                if (timer) timer.textContent = remaining > 0 ? `Preparation ${formatTime(remaining)}` : 'Starting…';
                if (remaining <= 0) {
                    beginSpeaking();
                    return;
                }
                timerId = window.setInterval(() => {
                    remaining -= 1;
                    if (timer) timer.textContent = `Preparation ${formatTime(Math.max(0, remaining))}`;
                    if (remaining <= 0) {
                        clearTimer();
                        beginSpeaking();
                    }
                }, 1000);
                setStatus('Preparation time. The speaking recording starts when the preparation timer ends.', 'text-primary');
            } catch (error) {
                releaseStream();
                showReady();
                setStatus('Microphone permission was not granted. You can still use the prompt, timer, notes, and self-review fields.', 'text-warning');
            }
        };

        if (start) start.addEventListener('click', startRecording);
        if (stop) stop.addEventListener('click', stopRecording);
        if (play) play.addEventListener('click', () => audio?.play());
        if (form) {
            form.addEventListener('submit', (event) => {
                const submitter = event.submitter;
                if (submitter?.value === 'submitted' && Number(durationInput?.value || 0) < 1) {
                    event.preventDefault();
                    setStatus('Make a local recording before submitting for self-review.', 'text-danger');
                }
            });
        }
        showReady();
    });
};

const initializeAppPage = () => {
    initializeAttemptPage();
    initializeWritingEditor();
    initializeSpeakingRecorder();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAppPage, { once: true });
} else {
    initializeAppPage();
}
