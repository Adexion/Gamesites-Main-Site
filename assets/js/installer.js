global.token = document.getElementById('tokenInstaller').innerHTML;
global.instalationError = false;
global.instalationErrorCode = '';
global.installationRun = false;
window.onbeforeunload = confirmExit;

const stepsList = [
    '/v1/setup/initialize',
    '/v1/setup/database',
    '/v1/setup/install',
    '/v1/setup/webpack',
    '/v1/setup/domain',
    '/v1/setup/configure'
];

document.getElementById('runInstaller').addEventListener('click', () => {
    document.getElementById('installer').classList.toggle('d-none');
    document.getElementById('btnInstaller').classList.toggle('d-none');

    startInstallApp().then(() => {
        document.getElementById('progressInstaller').classList.add('bg-success');
        document.getElementById('progressInstaller').classList.remove(' progress-bar-striped');
        document.getElementById('progressInstaller').classList.remove(' progress-bar-animated');

        setTimeout(() => {
            window.location.href = '/dashboard/application'
        }, 5000);
    });
});

function confirmExit() {
    if (global.installationRun) {
        return "Czy na pewno chcesz przerwać proces instalacji?";
    }
}

function setPercentage(percent) {
    const progress = document.getElementById('progressInstaller');

    progress.innerHTML = percent + '%';
    progress.setAttribute('aria-valuenow', percent);
    progress.setAttribute('aria-valuenow', percent);
    progress.setAttribute('style', `width: ${percent}%`);
}

function interpretativeResponse(json) {
    if (json === undefined) return;
    document.getElementById('titleInstaller').innerHTML = json.title;
    setPercentage(json.percentage);
}

async function startInstallApp() {
    global.installationRun = true;
    for (const url of stepsList) {
        await makeApiCall(url);
    }
    global.installationRun = false;
}

function checkErrorIfExist(error) {
    return error.status !== 200 || error.ok !== true;
}

async function makeApiCall(url) {
    if (global.instalationError) {
        return;
    }

    await api(url, global.token).then(response => {
        if (checkErrorIfExist(response)) {
            global.instalationError = true;
            global.instalationErrorCode = response.status;
            displayError();

            return;
        }

        response.json().then((r) => interpretativeResponse(r))
    });
}

function displayError() {
    document.getElementById('installer').classList.add('d-none');
    document.getElementById('installerError').classList.remove('d-none');
    document.getElementById('errorCode').innerHTML = global.instalationErrorCode;
}

function api(url, token, errors = false) {
    if (errors) {
        return {
            'title': "Error!!!"
        };
    }

    const controller = new AbortController();

    return fetch(url, {
        method: 'POST',
        body: JSON.stringify({'token': token}),
        headers: {
            'Content-Type': 'application/json',
        },
        signal: controller.signal
    });
}


