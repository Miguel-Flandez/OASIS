const homeButton = document.getElementById('home-button');
const opcButton = document.getElementById('opc-button')

homeButton.onclick = function(){
    window.location.href = 'studentHome.html';
}
opcButton.onclick = function(){
    window.location.href = 'online-payment-center.html';
}