module.exports = () => {
    fetch('/api/notification', {
        method: 'GET'
    })
        .then(response => response.json())
        .then(json => {
            Object.entries(json['notifications']).forEach(value => document.getElementById('notification').appendChild(buildNotification(value[1])));
        })
}

global.notificationCounter = 0;

function buildNotification(value) {
    const div = document.createElement('div');
    const title = document.createElement('div');
    const text = document.createElement('div');
    const date = document.createElement('p');

    title.innerText = value['title'];
    title.classList.add('fw-bold');
    title.classList.add('notification-title');

    text.innerHTML = title.outerHTML + value['text'].replace(/(<([^>]+)>)/gi, "");
    text.classList.add('notification-content');

    date.innerText = value['date'];
    date.classList.add('comment-meta');
    date.classList.add('mt-1');
    date.classList.add('notification-date');

    div.appendChild(text);
    div.appendChild(date);
    div.classList.add('notification')

    if (value['date'].split('.')[0] === new Date().getDate().toString()) {
        global.notificationCounter++;

        const dropdown = document.getElementById('notificationDropdown');
        if (notificationCounter === 1) {
            global.counter = document.createElement('div');
            global.counter.classList.add('notification-counter')

            dropdown.appendChild(global.counter);
        }

        global.counter.innerText = global.notificationCounter;
    }

    return div;
}