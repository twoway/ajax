<!DOCTYPE html>
<!--[if IEMobile 7 ]>    <html class="no-js iem7"> <![endif]-->
<!--[if (gt IEMobile 7)|!(IEMobile)]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title>WordNotes</title>
        
        <?php wp_head(); ?>

        <!-- This script prevents links from opening in Mobile Safari. https://gist.github.com/1042026 -->
        
        <script>(function(a,b,c){if(c in b&&b[c]){var d,e=a.location,f=/^(a|html)$/i;a.addEventListener("click",function(a){d=a.target;while(!f.test(d.nodeName))d=d.parentNode;"href"in d&&(d.href.indexOf("http")||~d.href.indexOf(e.host))&&(a.preventDefault(),e.href=d.href)},!1)}})(document,window.navigator,"standalone")</script>

    </head>
    <body <?php body_class(); ?>>

        <header class="header">
            <div class="toolbar">
                <span class="button left">
                    <a class="clear-notes action" href="#clear-notes" data-action="clear-all" data-modal="yn">Clear notes</a>
                </span>

                <h1>WordNotes</h1>
                
                <span class="button right group">
                    <a class="add-user action" href="#add-user" data-action="add-user" data-modal="invite">+</a>
                    <a class="users" href="#users">Users</a>
                </span>

                <ul class="all-users">
                    <?php $users = get_users(array('exclude' => get_current_user_id())); ?>
                    <?php if(empty($users)) echo '<li class="empty">No users yet! add some.</li>'; ?>
                    <?php foreach($users as $user) : ?>
                        <li id="user-<?php echo $user->ID; ?>">
                            <a class="delete-user action" href="#delete-user" data-action="delete-user" data-modal="yn" data-id="<?php echo $user->ID; ?>">remove user</a>
                            <?php echo $user->user_email; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </header>