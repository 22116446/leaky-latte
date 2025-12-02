if(document.readystate=='loading'){
	document.addEventListener('DOMcontentLoaded',ready)
}else{
	ready()
}

function ready(){
	var removecartitembuttons= document.getElementsByClassName('btn-danger')
    console.log(removecartitembuttons)
    for(var i=0;i<removecartitembuttons.length;i++){
	var button=removecartitembuttons[i]
	button.addEventListener('click',removecartitem)
	
	}
	
	var quantityinput=document.getElementsByClassName('cart-quantity-input')
	for(var i=0;i<quantityinput.length;i++){
		var input= quantityinput[i]
		input.addEventListener('change',quantitychanged)
	}
	var addtocartButtons=document.getElementsByClassName('shop-item-button')
	for(var i=0;i<addtocartButtons.length;i++){
		var button=addtocartButtons[i]
		button.addEventListener('click',addtocartclicked)
		
	}
	document.getElementsByClassName('btn-order')[0].addEventListener('click',orderclicked)
	
}
function orderclicked(){
	alert('Thank you for your order , your order will be delivered shortly')
	var cartitems=document.getElementsByClassName('cart-items')[0]
	while(cartitems.hasChildNodes()){
		cartitems.removeChild(cartitems.firstChild)
	}
	updatecarttotal()
}
function removecartitem(event){
	var buttonclicked=event.target
    buttonclicked.parentElement.parentElement.remove()
	updatecarttotal()
}
	
function quantitychanged(event){
	var input=event.target
	if(isNaN(input.value)||input.value<=0){
		input.value=1
	}
	updatecarttotal()
}
function addtocartclicked(event){
	var button=event.target
	var shopitem=button.parentElement.parentElement
	var title=shopitem.getElementsByClassName('shop-item-title')[0].innerText
	var price=shopitem.getElementsByClassName('shop-item-price')[0].innerText
	var imagesrc=shopitem.getElementsByClassName('shop-item-image')[0].src
	console.log(title,price,imagesrc)
	additemtocart(title,price,imagesrc)
	updatecarttotal()
	
}
function additemtocart(title,price,imagesrc){
	var cartrow=document.createElement('div')
	cartrow.classList.add('cart-row')
	var cartitems=document.getElementsByClassName('cart-items')[0]
	var cartitemNames=cartitems.getElementsByClassName('cart-item-title')
	for(var i=0;i<cartitemNames.length;i++){
		if(cartitemNames[i].innerText==title){
			alert('Its already added to the cart')
			return
		}
	}
	var cartrowcontents=`
	<div class="cart-item cart-column">
                        <img class="cart-item-image" src="${imagesrc}"  width="100" height="100">
                        <span class="cart-item-title">${title}</span>          
                    </div>
                    <span class="cart-price cart-column">${price}</span>
                    <div class="cart-quantity cart-column">
                        <input class="cart-quantity-input" type="number" value="1">
                        <button class="btn btn-danger" type="button">REMOVE</button>
                    </div>`
    
	cartrow.innerHTML=cartrowcontents
	cartitems.append(cartrow)
	cartrow.getElementsByClassName('btn-danger')[0].addEventListener('click',removecartitem)
	cartrow.getElementsByClassName('cart-quantity-input')[0].addEventListener('change',quantitychanged)
	
}

function updatecarttotal(){
	var cartitemcontainer = document.getElementsByClassName('cart-items')[0]
	var cartrows=cartitemcontainer.getElementsByClassName('cart-row')
	var total= 0
	for(var i=0;i<cartrows.length;i++){
		var cartrow=cartrows[i]
		var priceelement=cartrow.getElementsByClassName('cart-price')[0]
		var quatityelement=cartrow.getElementsByClassName('cart-quantity-input')[0]
		var price = parseFloat(priceelement.innerText.replace('$',''))
		var quantity= quatityelement.value
		total=total+(price*quantity)
		
		
	}
	total=Math.round(total*100)/100
	document.getElementsByClassName('cart-total-price')[0].innerText='₺'+total
}

