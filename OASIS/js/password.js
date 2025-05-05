
const passOldIcon = document.querySelector('.fa-solid.fa-key.old');
const passOld = document.getElementById('old-password')

const passNewIcon = document.querySelector('.fa-solid.fa-key.new');
const passNew = document.getElementById('new-password')

const passRetypeIcon = document.querySelector('.fa-solid.fa-key.retype');
const passRetype = document.getElementById('re-new-password')

document.querySelectorAll('.fa-solid.fa-key').forEach((element)=>{
    element.style.color = 'rgba(128, 128, 128, 0.6)';
})




 
passOldIcon.onclick = function(){
    passOld.type = passOld.type === 'password' ? 'text':'password';

    passOldIcon.style.color = passOldIcon.style.color === 'rgba(128, 128, 128, 0.6)'?'#40513b':'rgba(128, 128, 128, 0.6)'
}

passNewIcon.onclick = function(){
    passNew.type = passNew.type === 'password' ? 'text':'password';

    passNewIcon.style.color = passNewIcon.style.color === 'rgba(128, 128, 128, 0.6)'?'#40513b':'rgba(128, 128, 128, 0.6)'

}

passRetypeIcon.onclick = function(){
    passRetype.type = passRetype.type === 'password' ? 'text':'password';

    passRetypeIcon.style.color = passRetypeIcon.style.color === 'rgba(128, 128, 128, 0.6)'?'#40513b':'rgba(128, 128, 128, 0.6)'

}

