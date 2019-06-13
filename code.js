$('.preloader').hide();
var isLoad;
var isWait;
function ajaxRequest(address, async) {
    var xhr = new XMLHttpRequest();
    isLoad = false;
    loader();
    xhr.open('GET', 'support.php?address=' + address , async);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            isLoad = true;
            if (xhr.responseText)
            var info = (JSON.parse(xhr.responseText));
            console.log(info);
            addTextToHtml(info);
            if(isWait){
                isWait = false;
                loader();
            }
        }
    };
    xhr.send();
}
var arr = [];
var size = 0;
function addAddress(){
    if (arr.length === 10) {
        alert('Додано максимальну кількість адрес, будь ласка почніть сканування');
        return 0;
    }
    var isNew = true;
    var address = document.getElementById('inp').value;
    arr.forEach(function (value) {
        if(address === value)
            isNew = false;
    });
    if(isNew)
        arr.push(address);
    else {
        alert('Така адреса вже була додана!');
        return 0;
    }
    document.getElementsByClassName('inp-form')[0].reset();
    addListToHtml(arr);
}
function startScan() {
    if (arr.length === 0){
        alert('Будь ласка, додайте адресу');
        return 0;
    }
    ajaxRequest(arr, true);
    size = arr.length;
    arr = [];
}
function loader() {
    if (isLoad) {
        $('.preloader').hide();
    }
    else {
        $('.preloader').show();
        isWait = true;
    }
}
function addTextToHtml(info){
    var phpArray = ['error', 'scan', 'ref', 'sql'];
    console.log(info);
    $('.output').remove();
    var newDiv = document.createElement('div');
    newDiv.classList.add('output');
    var num1 = document.createElement('p');
    num1.classList.add('output__text');
    num1.innerText = "Елементів додано - " + size;
    newDiv.append(num1);
    var num2 = document.createElement('p');
    num2.classList.add('output__text');
    num2.innerText = "Елементів проскановано - " + size;
    newDiv.append(num2);
    for(var i = 0; i < phpArray.length; i++){
        if(info.entities[phpArray[i]]) {
            var p = document.createElement('p');
            p.classList.add('output__text');
            if (phpArray[i] === 'scan')
                p.innerText = "Знайдено вразливостей- " + info.entities[phpArray[i]];
            else  if (phpArray[i] === 'ref')
                p.innerText = "Сайти з вразливостями- " + info.entities[phpArray[i]];
            newDiv.append(p);
        }
    }
    $('.cont').append(newDiv);
}

function addListToHtml(arr){
    $('.output').remove();
    var newDiv = document.createElement('div');
    newDiv.classList.add('output');
    for(var i = 0; i < arr.length; i++){
        var p = document.createElement('p');
        p.classList.add('output__item');
        p.innerText = i+1 + '. ' + arr[i];
        newDiv.append(p);
    }
    $('.cont').append(newDiv);
}