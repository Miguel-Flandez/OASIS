const homeButton = document.getElementById('home-button');
const opcButton = document.getElementById('opc-button')
const studentInformationButton = document.getElementById('student-info-button')
const profileButton = document.getElementById('profile-button');

homeButton.onclick = function(){
    window.location.href = 'studentHome.html';
}
opcButton.onclick = function(){
    window.location.href = 'online-payment-center.html';
}
studentInformationButton.onclick = function(){
    window.location.href = 'studentInformation.html'
}
profileButton.onclick = function(){
    window.location.href = 'userProfile.html'
}

const sidebarToggle = document.querySelector('.fa-solid.fa-bars');
const sidebar = document.getElementById('sidebar');

sidebarToggle.onclick = function(){
    if(!sidebar.classList.contains('hide-sidebar')){
        sidebar.classList.add('hide-sidebar');
    }else{
        sidebar.classList.remove('hide-sidebar');
    }
}