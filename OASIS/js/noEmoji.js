const inputs = document.getElementsByClassName('input')

for (let input of inputs){
    input.addEventListener('input',()=>{
        input.value = input.value.replace(/[^\w\d`~!@#$%^&*()_\-+={[}\]|\\:;"'<,>.?/]/g, '');
    })
}