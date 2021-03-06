jQuery(function($){

  var Notes = {
    init: function(){
      Notes.usersMenu();
      Notes.listenForChanges();
      //actions call
      Notes.actions();
      //UISendRequest call
    },

    usersMenu: function(){
      $('.all-users').addClass('hidden');

      $('a.users').click(function(e){
        e.preventDefault()
        $('.all-users').toggleClass('hidden');
      });
    },

    listenForChanges: function(){
      $('.notes li input').focus(function(){
        $(this).parent('li').addClass('editing');
      }).blur(function(){
        $(this).parent('li').removeClass('editing');
      })

      $('.new-post input').focus(function(){
        $(this).val('');
      }).blur(function(){
        if( $(this).val() == "" ) $(this).val('New note...')
      });
    },

    //needs arguments
    openNotice: function(){
      $('.notice:visible').toggleClass('hidden').data('info', '');
      Notes.centreNotice();
      $('.'+noticeClass).toggleClass('hidden').data('info', data);
    },

    centreNotice: function(){
      var windowWidth  = $(window).width();
      var windowHeight = $(window).height();
      $('.notice').each(function(){
        var nw = $(this).width();
        var nh = $(this).height();

        var nl = (windowWidth/2) - (nw/2);
        var nt = (windowHeight/2) - (nh/2);

        $(this).css({left: nl, top: nt});
      });
    },

    actions: function(){
      $('.notice').addClass('hidden');

      $('.action').live('click', function(e){

        e.preventDefault();

        var action = 'notes-' + $(this).data('action');
        var modal  = $(this).data('modal');
        var id     = $(this).data('id');
        var text   = $(this).parent('li').find('input').val();

        var data = {
          action: action,
          id: id,
          text: text
        };

        if( modal === "" ){
          Notes.processRequest(data)
        } else if ( modal != "" ){
          Notes.openNotice(modal, data);
        }
      });
    },

    //create UISendRequest function

    //create processRequest function
    processRequest: function(data){
    console.log(data);
    $.post(
      WP_AJAX.ajaxurl,
      data,
      Notes.succcessfulRequest
    );

    //create successfulRequest function
    succcessfulRequest: function(jsonResponse){
    alert('Request sent!');
   
    //change UI based on response from WordPress
    }, 
  }

  $(document).ready(function(){
    Notes.init();
  });
})