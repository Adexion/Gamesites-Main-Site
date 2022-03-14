const token = document.getElementById('tokenInstaller').innerHTML;

document.getElementById('runInstaller').addEventListener('click', () => {
    document.getElementById('installer').classList.toggle('d-none');
    document.getElementById('btnInstaller').classList.toggle('d-none');

    steps().then(() => {});
});

function setPercentage(percent) {
    const progress = document.getElementById('progressInstaller');

    progress.innerHTML = percent + '%';
    progress.setAttribute('aria-valuenow', percent);
    progress.setAttribute('aria-valuenow', percent);
    progress.setAttribute('style', `width: ${percent}%`);
}

function interpretativeResponse(json) {
    console.log(json);
    document.getElementById('titleInstaller').innerHTML = json.title;
    setPercentage(json.percentage);
}

async function steps() {

    let errors = false;
    await api('/v1/setup/initialize', token).catch(() => {alert('DUPA');errors = true;}).then(json => interpretativeResponse(json));
    await api('/v1/setup/database', token, errors).catch(() => {alert('DUPA');errors = true;}).then(json => interpretativeResponse(json));
    await api('/v1/setup/install', token, errors).catch(() => {alert('DUPA');errors = true;}).then(json => interpretativeResponse(json));
    await api('/v1/setup/webpack', token, errors).catch(() => {alert('DUPA');errors = true;}).then(json => interpretativeResponse(json));
    await api('/v1/setup/domain', token, errors).catch(() => {alert('DUPA');errors = true;}).then(json => interpretativeResponse(json));
    await api('/v1/setup/configure', token, errors).catch(() => {alert('DUPA');errors = true;}).then(json => {
        interpretativeResponse(json);

        if (json.style === 'success') {
            document.getElementById('progressInstaller').classList.add('bg-success');
        }
    });
}

async function api(url, token, errors = false) {
    if (errors) {
        return {};
    }

    const { timeout = 6000 } = options;
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeout);

    const response = await fetch(url, {
        method: 'POST',
        body: JSON.stringify({'token': token}),
        headers: {
            'Content-Type': 'application/json',
        },
        signal: controller.signal
    });
    clearTimeout(id);

    return response.json();
}


