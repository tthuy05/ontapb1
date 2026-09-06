import 'bootstrap';

const formatSavedAt = (value) => {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? '' : date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

document.addEventListener('DOMContentLoaded', () => {
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
});
