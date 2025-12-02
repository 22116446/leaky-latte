document.getElementById('contact-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const res = await fetch('save_contact.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            alert('Thanks for your message!');
            this.reset();
        } else {
            alert('Could not send message. Please try again.');
        }
    } catch (err) {
        console.error(err);
        alert('Network error, please try again.');
    }
});
