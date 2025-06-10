{const menuItem = document.getElementById('menuItem');
const dropdownMenu = document.getElementById('dropdownMenu');
let timeout;

menuItem.addEventListener('mouseenter', function() {
  timeout = setTimeout(() => {
    dropdownMenu.classList.add('show'); 
  }, 300); 
  
});


menuItem.addEventListener('mouseleave', function() {
  clearTimeout(timeout); 
  dropdownMenu.classList.remove('show');  
});}  

{
const menuItem1 = document.getElementById('menuItemDonna');
const dropdownMenu1 = document.getElementById('dropdownMenuDonna');

  menuItem1.addEventListener('mouseenter', function() {
  timeout = setTimeout(() => {
  dropdownMenu1.classList.add('show'); 
  },300);
});


menuItem1.addEventListener('mouseleave', function() {
  clearTimeout(timeout); 
  dropdownMenu1.classList.remove('show'); 
});
}


{const menuItem2 = document.getElementById('menuItemBamb');
const dropdownMenu2 = document.getElementById('dropdownMenuBamb');


menuItem2.addEventListener('mouseenter', function() {
  timeout = setTimeout(() => {
  dropdownMenu2.classList.add('show'); 
  },300);
});


menuItem2.addEventListener('mouseleave', function() {
  clearTimeout(timeout); 
  dropdownMenu2.classList.remove('show');  
});

}


{const menuItem3 = document.getElementById('menuMobile');
const dropdownMenu3 = document.getElementById('dropdownMenuMobile');
let timeout3;

const openMenu = () => {
  timeout3 = setTimeout(() => {
    dropdownMenu3.classList.add('show'); 
  }, 300); 
}

const closeMenu = () => {
  clearTimeout(timeout3); 
  dropdownMenu3.classList.remove('show');  
}

menuItem3.addEventListener('click', function() {
  if (dropdownMenu3.classList.contains('show')) {
    closeMenu(); 
  } else {
    openMenu(); 
  }
});
}
//bottone add remove deelay mobile

//bottone toggle login

{
  const navBar = document.getElementById('main');

  window.addEventListener('scroll', function() {
    if (window.scrollY > 50) {
      navBar.classList.add('scrolled');
    } else {
      navBar.classList.remove('scrolled');
    }
  });
}
//scroll navbar


{
  const prevButton = document.getElementById('prev');
  const nextButton = document.getElementById('next');
  const carousel =document.getElementById('carousel');
  let currentIndex = 0;

  prevButton.addEventListener('click', function() {
    const indexMax = carousel.children.length;
    currentIndex = (currentIndex - 1 + indexMax) % indexMax; 
    updateCarousel();
  });

  nextButton.addEventListener('click', function() {
    const indexMax = carousel.children.length;
    currentIndex = (currentIndex + 1) % indexMax; 
    updateCarousel();
  });

  function updateCarousel(){
    const boxWidth = carousel.children[0].offsetWidth; 
    const gap = parseFloat(getComputedStyle(carousel.children[0]).marginRight); 
    const offset = -currentIndex * (boxWidth+ gap); 
    carousel.style.transform = `translateX(${offset}px)`;
    carousel.style.transition = 'transform 0.7s ease-in-out'; 

  }
}//carousel


//click bottone navbianca
{
  const navbar = document.getElementById('main'); // Seleziona la navbar
  const dropdownMenus = document.querySelectorAll('.dropdown-menu'); 
  const menuButton = document.getElementById('menuMobile'); 
  

  menuButton.addEventListener('click', () => {
    navbar.classList.toggle('navbar-white');
  });

  const addNavbarWhite = () => {
    navbar.classList.add('navbar-white');
  };

  
  const removeNavbarWhite = () => {
    navbar.classList.remove('navbar-white');
  };

  
  navbar.addEventListener('mouseenter', () => {
    addNavbarWhite(); 
  });

  navbar.addEventListener('mouseleave', () => {
    
    const isMouseOverDropdown = Array.from(dropdownMenus).some(dropdown =>
      dropdown.matches(':hover')
    );

    if (!isMouseOverDropdown) {
      removeNavbarWhite(); 
    }
  });

 
  dropdownMenus.forEach(dropdown => {
    dropdown.addEventListener('mouseenter', () => {
      addNavbarWhite(); 
    });

    dropdown.addEventListener('mouseleave', () => {
      
      const isMouseOverNavbar = navbar.matches(':hover');

      if (!isMouseOverNavbar) {
        removeNavbarWhite(); 
      }
    });
  });
  
}


//comportamento dei punti
{
  const points = document.querySelectorAll('.points span'); 
  const boxes = document.querySelectorAll('.boxes'); 
  const carousel = document.getElementById('carousel'); 

  
  const updateCarousel = (index) => {
    const boxWidth = boxes[0].offsetWidth; 
    const gap = parseFloat(getComputedStyle(boxes[0]).marginRight); 
    const offset = -index * (boxWidth + gap); 
    carousel.style.transform = `translateX(${offset}px)`; 
    carousel.style.transition = 'transform 0.7s ease-in-out'; 

   
    points.forEach(point => point.classList.remove('active'));
    points[index].classList.add('active');
  };

  
  points.forEach((point, index) => {
    point.addEventListener('click', () => {
      updateCarousel(index); 
    });
  });
}





 
{
  // Sistema di ricerca per navbar - Codice JavaScript completo
// Da includere nelle pagine che hanno la navbar con il campo di ricerca


// Fine del codice di ricerca per navbar
}
