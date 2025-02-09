const homeButton = document.getElementById('home-button');
const opcButton = document.getElementById('opc-button')
const profileButton = document.getElementById('profile-button');

homeButton.onclick = function(){
    window.location.href = 'student-home.html';
}
opcButton.onclick = function(){
    window.location.href = 'online-payment-center.html';
}

profileButton.onclick = function(){
    window.location.href = 'user-profile.html'
}

/*
const sidebarToggle = document.querySelector('.fa-solid.fa-bars');
const sidebar = document.getElementById('sidebar');

sidebarToggle.onclick = function(){
    if(!sidebar.classList.contains('hide-sidebar')){
        sidebar.classList.add('hide-sidebar');
    }else{
        sidebar.classList.remove('hide-sidebar');
    }
}
*/

const toAdmin = document.getElementById('to-admin');

toAdmin.onclick = function(){
    window.location.href = 'admin-home.html'
}