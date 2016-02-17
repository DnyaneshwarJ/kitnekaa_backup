jQuery(document).ready(function($){
  function calCateHeight(){
    var mainCateHeight = $('#yt_header_left').height();
    $('.sm_megamenu_dropdown_1column ').css({'min-height':mainCateHeight})
  };

  $(window).load(function(){
    calCateHeight();
  });

  $('.sambar ul li').hover(function(){
    setTimeout(function(){
      var mainCateHeight = $('.sm_megamenu_wrapper_vertical_menu').height();
      $('.sm_megamenu_dropdown_1column ').css({'min-height':mainCateHeight})
    },500)
  });


  $("#narrow-by-list ol li a").click(function() {
    $(this).toggleClass('checked');
  });

  //Side Category Filter
  var sideCateParent = $('.narrow-by-list'),
  FilterList = $('#narrow-by-list ol .filter_list');
  FilterList.each(function(){
    console.log('Pradeep');
    var thisList = $(this);
    thisList.children('li').hide().filter(':lt(4)').show();
  });

  FilterList.find('.morefilter').on('click',function(){
    $(this).siblings(':gt(3)').slideToggle();
    if($(this).text() == 'Less'){
      $(this).html('More')
    }else if($(this).text() == 'More'){
      $(this).html('Less')
    }
  });

  var filterListHeight = $('.filter_list li').height();
  //Expand Function
  $.fn.clickToggle = function(func1, func2) {
    var funcs = [func1, func2];
    this.data('toggleclicked', 0);
    this.click(function() {
      var data = $(this).data();
      var tc = data.toggleclicked;
      $.proxy(funcs[tc], this)();
      data.toggleclicked = (tc + 1) % 2;
    });
    return this;
  };

  // $('ol').on('click','span.expandfilter',function() {
  //   // var hello = $(this).hasClass('expand-active');
  //   // console.log(hello);
  //   // if(hello){
  //   //   console.log('In if');
  //   //   $(this).parent('ol').find('.filter_list').stop().slideUp();
  //   //   $(this).addClass('expand-active');
  //   // }else{
  //   //   console.log('In else');
  //   //   $(this).parent('ol').find('.filter_list li').show();
  //   //   $(this).parent('ol').find('.filter_list .morefilter').html('Less')
  //   //   $(this).parent('ol').find('.filter_list').stop().slideDown();
  //   //   $(this).removeClass('expand-active');
  //   // }
  // });

  // $('.expandfilter').clickToggle(function() {
  //   $(this).parent('ol').find('.filter_list').stop().slideUp();
  //   $(this).addClass('expand-active');
  // }, function() {
  //   $(this).parent('ol').find('.filter_list li').show();
  //   $(this).parent('ol').find('.filter_list .morefilter').html('Less')
  //   $(this).parent('ol').find('.filter_list').stop().slideDown();
  //   $(this).removeClass('expand-active');
  // });

  $(window).resize(function(){
    var winWidth = $(window).width(),
    banimgHeight = $('#slider1 > img').height();
    
    //if(winWidth <= 767){
    $('#slider1').css({'height':banimgHeight})
    //}
  });

  var sliderinterval;
  sliderinterval = setInterval(slidemoveFun, 3000);

  function slidemoveFun(){
    $('#next').trigger('click');
  }

  //get new arrival offset position
  //var newarrivalPosition = $('.yt-tab-listing.first-load').offset().top;

  $(window).load(function(){
    $('#thumb li').click(function(){
      clearInterval(sliderinterval );
      sliderinterval = setInterval(slidemoveFun, 3000)
    });

    $('#thumb li:last').click(function(){
    //$('body,html').animate({scrollTop: newarrivalPosition}, 1000);
    });
  });
})
// var $ = jQuery.noConflict();
