function updateDateTime() {
    const now = new Date();

    const day = String(now.getDate()).padStart(2, '0'); 
    const month = String(now.getMonth() + 1).padStart(2, '0'); 
    const year = now.getFullYear(); 

    const hours = String(now.getHours()).padStart(2, '0'); 
    const minutes = String(now.getMinutes()).padStart(2, '0'); 
    const seconds = String(now.getSeconds()).padStart(2, '0'); 

    const formattedDateTime = `${day}/${month}/${year} | ${hours}:${minutes}:${seconds}`;

    document.getElementById('date-and-time').textContent = formattedDateTime;

}

// Update every second
setInterval(updateDateTime, 1000);

// Run it once on load
updateDateTime();

const dropdown = document.getElementById('data-analytics');
const dashboard = document.getElementById('dashboard-info');
const data = document.getElementById('graph-container');

data.classList.add('hide');

dropdown.addEventListener('change', function() {
    if (dropdown.value === 'statistics') {
        dashboard.classList.remove('hide');
        data.classList.add('hide');
    } else if (dropdown.value === 'payment-trends') {
        data.classList.remove('hide');
        dashboard.classList.add('hide');
    }
});

const chat = document.querySelector('.fa-solid.fa-comment-dots');
const chatbox = document.querySelector('.chatbox');

chat.onclick = function(){
    if(chatbox.classList.contains('hide')){
        chatbox.classList.remove('hide');
    }else{
        chatbox.classList.add('hide');
    }
}

