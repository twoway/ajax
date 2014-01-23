jQuery(function($){

  var Notes = {
    init: function(){
      Notes.usersMenu();
      Notes.listenForChanges();
      Notes.actions();
      Notes.UISendRequest();
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

    openNotice: function(noticeClass, data){
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

    UISendRequest: function(){
      $('.go').click(function(){
        var data = $(this).parents('.notice').data('info');
        if($(this).parents('.notice').hasClass('invite')) 
          data.text = $('.invite-email').val();
        Notes.processRequest(data);
        $('.notice:visible').toggleClass('hidden').data('info', '');
      });

      $('.cancel').click(function(){
        $(this).parents('.notice').toggleClass('hidden').data('info', '');
      });
    },

    processRequest: function(data){
      console.log(data);
      $.post(
        WP_AJAX.ajaxurl,
        data,
        Notes.succcessfulRequest
      );
    },

    succcessfulRequest: function(jsonResponse){
      //action's effect on UI

      response = jQuery.parseJSON(jsonResponse);

      console.log(response.message);

      switch (response.message){

        case "fail" : alert('Something went wrong.'); break;

        case "user-added" :

          console.log('user with email address ' + response.text + ' has been added to WordNotes');

          var user_tmpl = $('#tmpl-user').html();
          var user_data = {
            id: response.id,
            email: response.text
          }

          if($('.all-users .empty').length > 0) $('.all-users .empty').remove();

          $('.all-users').append(Mustache.render(user_tmpl, user_data));

        break;

        case "user-deleted" :
          $('#user-' + response.id).remove();
          if($('.all-users li').length == 0) $('.all-users').append('<li class="empty">No users yet! add some.</li>'); 
        break;

        case "all-deleted" :
          $('.note').remove();
          alert('All notes have been deleted.');
        break;

        case "post-added" :

          console.log('Note added!');

          var post_tmpl = $('#tmpl-post').html();
          var post_data = {
            id: response.id,
            post_title: response.text
          };

          $(Mustache.render(post_tmpl, post_data)).insertBefore('.notes ul .new-post');

          $('.new-post input').blur().val('New note...');

        break;

        case "post-updated" :

          $('#post-' + response.id + ' input').blur();

        break;

        case "post-deleted" :

          $('#post-' + response.id).remove();

        break;
      }

    },
  }

  $(document).ready(function(){
    Notes.init();
  });
})