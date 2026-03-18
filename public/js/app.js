// MOBILE MENU
function toggleMobileMenu(){

const menu = document.getElementById('mobileMenu');
const icon = document.getElementById('menuIcon');

menu.classList.toggle('hidden');

icon.className = menu.classList.contains('hidden')
? 'fa-solid fa-bars-staggered'
: 'fa-solid fa-xmark';

}

// DESKTOP TOOLS DROPDOWN
function toggleToolsMenu(){

const menu = document.getElementById('toolsDropdown');
const arrow = document.getElementById('toolsArrow');

menu.classList.toggle('show');

arrow.classList.toggle('rotate-180');

}

// MOBILE TOOLS
function toggleMobileTools(){

const menu = document.getElementById('mobileToolsMenu');
const arrow = document.getElementById('mobileToolsArrow');

menu.classList.toggle('hidden');
arrow.classList.toggle('rotate-180');

}

// CLOSE DROPDOWN OUTSIDE CLICK
document.addEventListener('click', function(e){

const menu = document.getElementById('toolsDropdown');
const tools = document.getElementById('toolsMenu');

if(!tools.contains(e.target)){
menu.classList.remove('show');
}

});