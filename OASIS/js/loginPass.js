const loginPassIcon = document.getElementById('login-passIcon')
const loginPassword = document.getElementById('password');

loginPassIcon.onclick = function(){
    loginPassword.type = loginPassword.type === 'password' ? 'text':'password';

    loginPassIcon.style.color = loginPassIcon.style.color === 'rgba(128, 128, 128, 0.6)'?'#40513b':'rgba(128, 128, 128, 0.6)'
}