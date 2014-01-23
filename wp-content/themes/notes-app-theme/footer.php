        <!-- MODAL BOXES FOR MESSAGES -->

        <div class="notice yn">
            <p class="message">Are you sure?</p>
            <button class="yes go">Yes</button>
            <button class="no cancel">No</button>
        </div>

        <div class="notice invite">
            <p>Invite user to see your notes</p>
            <p><input type="email" class="invite-email" placeholder="your-mum@notes.com"></p>
            <p><button class="send-invite go">Invite</button></p>
        </div>

        <!-- TEMPLATES FOR USERS AND NOTES FOR LIVE INSERTION -->

        <script id="tmpl-user" type="text/html">
            <li id="user-{{id}}">
                <a class="delete-user action" href="#delete-user" data-action="delete-user" data-modal="yn" data-id="{{id}}">remove user</a>
                {{email}}
            </li>
        </script>

        <script id="tmpl-post" type="text/html">
            <li class="note" id="post-{{id}}">
                <a class="update-post action" href="#update-post" data-modal="" data-action="update-post", data-id="{{id}}">update</a>
                <a class="delete-post action" href="#delete-post" data-modal="" data-action="delete-post" data-id="{{id}}">delete</a>
                <input type="text" value="{{post_title}}" maxlength="140">
            </li>
        </script>

        <?php wp_footer(); ?>
    </body>
</html>
