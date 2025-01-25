const homeButtn = document.getElementById('home-button');
const opcButton = document.getElementById('opc-button')

homeButtn.onclick = function(){
    window.location.href = '../index.html';
}
opcButton.onclick = function(){
    window.location.href = 'html/online-payment-center.html';
}