(function () {
    const form = document.getElementById('processing-form');
    const convertButton = document.getElementById('pdfa-convert-button');
    const statusBox = document.getElementById('conversion-status');
    const statusText = document.getElementById('conversion-status-text');
    const statusIcon = document.getElementById('conversion-status-icon');
    const resultBox = document.getElementById('conversion-result');
    const detailsBox = document.getElementById('conversion-details');

    if (!form || !convertButton || !statusBox || !statusText || !statusIcon) {
        return;
    }

    const showBox = (element) => {
        element.classList.remove('conversion-hidden');
    };

    const hideBox = (element) => {
        element.classList.add('conversion-hidden');
    };

    const resetIcon = (classes) => {
        statusIcon.className = classes.join(' ');
    };

    const setStatus = (type, text, spinning = false) => {
        const alertClasses = ['alert', `alert-${type}`];
        statusBox.className = alertClasses.join(' ');
        statusText.textContent = text;

        const iconClasses = ['glyphicon'];
        if (type === 'success') {
            iconClasses.push('glyphicon-ok');
        } else if (type === 'danger') {
            iconClasses.push('glyphicon-remove');
        } else {
            iconClasses.push('glyphicon-refresh');
        }

        if (spinning) {
            iconClasses.push('conversion-spinner');
        }

        resetIcon(iconClasses);
        showBox(statusBox);
    };

    const renderDownload = (data) => {
        if (!data || !data.downloadUrl) {
            resultBox.innerHTML = '';
            hideBox(resultBox);
            return;
        }

        const displayName = data.displayName ? ` (${data.displayName})` : '';
        resultBox.innerHTML = `
            <div class="alert alert-success">
                <span class="glyphicon glyphicon-download"></span>
                <a href="${data.downloadUrl}" class="btn btn-info btn-sm top-buffer">${resultBox.dataset.downloadLabel}</a>
                <span class="text-info">${resultBox.dataset.readyLabel}${displayName}</span>
            </div>
        `;
        showBox(resultBox);
    };

    const updateDetails = (text) => {
        if (text) {
            detailsBox.textContent = text;
            showBox(detailsBox);
        } else {
            detailsBox.textContent = '';
            hideBox(detailsBox);
        }
    };

    let statusPoller = null;

    const stopPolling = () => {
        if (statusPoller !== null) {
            clearInterval(statusPoller);
            statusPoller = null;
        }
    };

    const pollStatus = (successText, failedText) => {
        if (statusPoller !== null) {
            return;
        }

        statusPoller = setInterval(() => {
            fetch('conversion_status.php', { credentials: 'same-origin' })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        setStatus('success', data.message || successText, false);
                        renderDownload(data);
                        stopPolling();
                    } else if (data.status === 'error') {
                        setStatus('danger', data.message || failedText, false);
                        stopPolling();
                    }
                })
                .catch(() => {
                    // Ignore polling errors; the main request will handle failures.
                });
        }, 1000);
    };

    convertButton.addEventListener('click', (event) => {
        event.preventDefault();

        const inProgressText = statusBox.dataset.inProgress;
        const successText = statusBox.dataset.success;
        const failedText = statusBox.dataset.failed;

        const formData = new FormData(form);
        formData.set('pdfa_convert', '1');

        convertButton.disabled = true;
        setStatus('info', inProgressText, true);
        hideBox(resultBox);
        hideBox(detailsBox);

        pollStatus(successText, failedText);

        fetch('convert.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((data) => {
                stopPolling();
                if (data.status === 'success') {
                    setStatus('success', data.message || successText, false);
                } else {
                    setStatus('danger', data.message || failedText, false);
                }

                renderDownload(data);
                updateDetails(data.returnValue || '');
            })
            .catch(() => {
                setStatus('danger', failedText, false);
            })
            .finally(() => {
                stopPolling();
                convertButton.disabled = false;
            });
    });
})();
