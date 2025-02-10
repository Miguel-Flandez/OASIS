const bell = document.querySelector('.fa-solid.fa-bell')
const notifBar = document.getElementById('notif-modal')

bell.onclick = function(){
    if(notifBar.classList.contains('hide')){
        notifBar.classList.remove('hide');
    }else{
        notifBar.classList.add('hide');
    }
}

