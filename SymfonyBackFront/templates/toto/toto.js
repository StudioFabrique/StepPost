

const btn = document.getElementById('toto');
console.log(btn);
btn.addEventListener('click', async () => {
    console.log('click');
    const response = await (await fetch('https://step-post-nodejs.herokuapp.com/admin/login', {
        method: 'POST',
        body: JSON.stringify({
            username: 'toto@toto.fr', password: 'Abcd@1234'
        }),
        headers: { 'Content-Type': 'application/json' }
    })).json()
    console.log(response);
})