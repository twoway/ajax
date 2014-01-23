# ACF { Edit Frontend Posts

A framework to allow users to edit posts in various post types on the front end.

## Compatibility

This add-on will work with:

* version 4 and up

## Installation

This add-on can be treated as a WP plugin.

### Install as Plugin

1. Copy the folder into your plugins folder
2. Activate the plugin via the Plugins admin page

## Use

### Create Post Types

Use `acf_frontend_edit_posts::register_post_type()` to register your post types. It works the same as `register_post_type()`, but also registers the new post type with this plugin. There is one extra parameter: `edit_page`.

** edit_page **

If you add a post slug or post id to the `edit_page` parameter, it will assign a page to use for editing the posts in the post type. So you can put a specific form on a specific page to edit specific post types.

### Add Form to a Page

There are two functions to get a form working on a page template. This is the same as ACF, and these functions wrap the native ACF functions, but add more features.

```
#!php

acf_frontend_edit_posts::acf_form_head( $post_type='' );
```
`$post_type` can be used to assign a post type to this form.

```
#!php

acf_frontend_edit_posts::acf_form();
```

### Get Links to User's Posts

You can list all of the links to posts currently assigned to a user.

```
#!php

acf_frontend_edit_posts::edit_link( $args='' );
```

There are currently 3 arguments that can be specified in an array or query string.

* `post_type` To only show links in a specific post type. Default: false
* `edit_page` Assign an edit page. Will override post type settings. Default: false
* `echo` Echo or return links. Default: true

### Turn Off Publish Button

```
#!php

add_filter( 'acf/frontend_edit_posts/button/publish', '__return_false' );
```

### Turn Off View Post Link

```
#!php

add_filter( 'acf/frontend_edit_posts/button/view', '__return_false' );
```

## Note on File Access

When creating an image or file upload field for use on the frontend, take note that Advanced Custom Fields has a field setting to restrict user's Media Library access to only images and files that have been uploaded to the current post, this is usually ideal.

To make sure this restriction is used, when setting up the field, under "Library", click on "Uploaded to post" instead of "All".

## Note on Permissions

Users who are attempting to upload a file to your site using the Media Library require 3 specific capabilities:

1. edit_others_pages
1. edit_published_pages
1. upload_files

I don't know why those first two are specifically required, but they are. If you want to allow subscribers to upload a file, you will have to give them those capabilities.
