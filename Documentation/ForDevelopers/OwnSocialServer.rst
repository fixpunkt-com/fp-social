..  _own-social-server:

=====================================
Operating your own Social Server
=====================================

Access to the social networks is performed exclusively through a
*Social Server*. The extension itself never communicates directly with the
individual networks, but only sends requests to the Social Server configured in
the :ref:`extension settings <social-server-settings>`.

By default, the SaaS service operated by fixpunkt is used. Costs may apply for
its use.

If you do not want to use this service, you can develop and operate your own
**Social Server**. Afterwards, enter its URL as ``apiUrl`` in the extension
settings. This chapter describes which interface such a server must provide.

Authentication
=================

All requests to the Social Server are ``POST`` requests to the respective
endpoint URL. This URL is composed of the configured ``apiUrl`` and the path of
the endpoint, e. g.::

    https://my-server.example.com/api/ + networks/facebook/posts

The parameters are passed as ``form_params`` (``application/x-www-form-urlencoded``).
In addition to the endpoint-specific parameters, every request contains the
following fields:

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   -   Parameter
        -   Description
    *   -   ``version``
        -   Version of the interface. Currently always ``2``. This corresponds
            to the namespace ``v2`` of the classes mentioned further below.
    *   -   ``auth[username]``
        -   The username stored in the access credentials.
    *   -   ``auth[accesstoken]``
        -   The access token stored in the access credentials.

The server is responsible for verifying ``auth[username]`` and
``auth[accesstoken]``. With your own server, you assign these credentials
yourself.

Expected response format
========================

The server's response must be a JSON that can be deserialized by the extension,
with the help of the extension ``fixpunkt/fp-social-bridge``, into one of the
following response objects:

..  list-table::
    :header-rows: 1
    :widths: 50 50

    *   -   Class
        -   Usage
    *   -   ``Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostsResponse``
        -   Response for a list endpoint (multiple posts).
    *   -   ``Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostResponse``
        -   Response for an endpoint for a single post.
    *   -   ``Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse``
        -   Response in case of an error.

The individual posts are expected as objects of type
``Fixpunkt\FpSocialBridge\v2\Data\Post``.

..  important::
    The exact structure of these classes – and thus the exact structure of the
    JSON to be returned – is not documented here, but in the extension
    ``fixpunkt/fp-social-bridge``. When implementing your server, use that
    extension as a reference.

Endpoints
=========

For each network, endpoints are needed for reading a list of posts as well as
for reading a single post. Each endpoint is described individually below.

The JSON examples each show the complete ``POST`` body including the
``version`` and ``auth`` fields described under `Authentication`_, so they can
be copied directly. Depending on the endpoint, the response is expected to be
either a list of posts (``SocialServerPostsResponse``) or a single post
(``SocialServerPostResponse``); the exact format is described in the section
`Expected response format`_.

..  note::
    You only need to implement those endpoints whose networks you actually want
    to use. If an account of an unsupported network is synchronized, only its
    synchronization fails.

Facebook
--------

``networks/facebook/posts``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads the posts of a Facebook page.

``POST`` request to ``networks/facebook/posts``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "pageId": "Facebook page ID"
    }

*   ``pageId`` – ID of the Facebook page from which the posts are read.
    Corresponds to the channel stored in the account.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/facebook/post``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads a single Facebook post.

``POST`` request to ``networks/facebook/post``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "postId": "Post ID"
    }

*   ``postId`` – ID of the post. Corresponds to the ID under which the post was
    stored when it was read.

**Response:** Single post (``SocialServerPostResponse``).

Instagram
---------

Depending on whether the account is operated in *Profile* or *Hashtag* mode,
either ``networks/instagram/posts`` or ``networks/instagram/hashtag`` is
requested to read the list.

``networks/instagram/posts``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads the posts of an Instagram profile (*Profile* mode).

``POST`` request to ``networks/instagram/posts``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "pageId": "Instagram profile ID"
    }

*   ``pageId`` – ID of the Instagram profile from which the posts are read.
    Corresponds to the channel stored in the account.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/instagram/hashtag``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads posts for a hashtag (*Hashtag* mode).

``POST`` request to ``networks/instagram/hashtag``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "pageId": "Instagram profile ID",
        "hashtag": "fixpunkt",
        "mode": "recent_media"
    }

*   ``pageId`` – ID of the Instagram profile through which the search is
    performed. Corresponds to the channel stored in the account.
*   ``hashtag`` – The hashtag to search for (without ``#``).
*   ``mode`` – Search mode for the hashtag. Possible values:

    *   ``recent_media`` – the most recent posts for the hashtag.
    *   ``top_media`` – the top posts for the hashtag.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/instagram/post``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads a single Instagram post.

``POST`` request to ``networks/instagram/post``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "postId": "Post ID"
    }

*   ``postId`` – ID of the post. Corresponds to the ID under which the post was
    stored when it was read.

**Response:** Single post (``SocialServerPostResponse``).

LinkedIn
--------

Depending on whether the account is operated in *Shares* or *UGC Posts* mode,
either ``networks/linkedin/posts`` or ``networks/linkedin/ugcPosts`` is
requested to read the list.

``networks/linkedin/posts``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads the posts of a LinkedIn page (*Shares* mode).

``POST`` request to ``networks/linkedin/posts``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "pageId": "LinkedIn page ID"
    }

*   ``pageId`` – ID of the LinkedIn page from which the posts are read.
    Corresponds to the channel stored in the account.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/linkedin/ugcPosts``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads the user-generated-content posts of a LinkedIn page
(*UGC Posts* mode).

``POST`` request to ``networks/linkedin/ugcPosts``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "pageId": "LinkedIn page ID"
    }

*   ``pageId`` – ID of the LinkedIn page from which the posts are read.
    Corresponds to the channel stored in the account.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/linkedin/post``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads a single LinkedIn post.

``POST`` request to ``networks/linkedin/post``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "postId": "Post ID"
    }

*   ``postId`` – ID of the post. Corresponds to the ID under which the post was
    stored when it was read.

**Response:** Single post (``SocialServerPostResponse``).

Youtube
-------

``networks/youtube/videos``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads the videos of a YouTube channel.

``POST`` request to ``networks/youtube/videos``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "channel": "YouTube channel ID"
    }

*   ``channel`` – ID of the YouTube channel from which the videos are read.
    Corresponds to the channel stored in the account.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/youtube/video``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads a single YouTube video.

``POST`` request to ``networks/youtube/video``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "videoId": "Video ID"
    }

*   ``videoId`` – ID of the video. Corresponds to the ID under which the video
    was stored when it was read.

**Response:** Single post (``SocialServerPostResponse``).

Wordpress
---------

Depending on the selected mode of the account, one of the following three
endpoints is requested to read the list.

``networks/wordpress/posts``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads all posts of a Wordpress blog.

``POST`` request to ``networks/wordpress/posts``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "baseUrl": "https://my-blog.example.com"
    }

*   ``baseUrl`` – Base URL of the Wordpress blog from which the posts are read.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/wordpress/postsWithTag``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads the posts of a Wordpress blog that are tagged with a specific tag.

``POST`` request to ``networks/wordpress/postsWithTag``. The body is transmitted
as ``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "baseUrl": "https://my-blog.example.com",
        "tag": "Tag"
    }

*   ``baseUrl`` – Base URL of the Wordpress blog.
*   ``tag`` – Tag by which the posts are filtered.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/wordpress/postsFromAuthor``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads the posts of a specific author of a Wordpress blog.

``POST`` request to ``networks/wordpress/postsFromAuthor``. The body is
transmitted as ``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "baseUrl": "https://my-blog.example.com",
        "author": "Author"
    }

*   ``baseUrl`` – Base URL of the Wordpress blog.
*   ``author`` – Author by which the posts are filtered.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/wordpress/post``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads a single Wordpress post.

``POST`` request to ``networks/wordpress/post``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "postId": "Post ID",
        "baseUrl": "https://my-blog.example.com"
    }

*   ``postId`` – ID of the post. Corresponds to the ID under which the post was
    stored when it was read.
*   ``baseUrl`` – Base URL of the Wordpress blog to which the post belongs.

**Response:** Single post (``SocialServerPostResponse``).

Bluesky
-------

``networks/bluesky/posts``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads the posts of a Bluesky profile.

``POST`` request to ``networks/bluesky/posts``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "clientHandle": "profil.bsky.social"
    }

*   ``clientHandle`` – Handle of the Bluesky profile from which the posts are
    read. Corresponds to the channel stored in the account.

**Response:** List of posts (``SocialServerPostsResponse``).

``networks/bluesky/post``
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Reads a single Bluesky post.

``POST`` request to ``networks/bluesky/post``. The body is transmitted as
``form_params`` (``application/x-www-form-urlencoded``):

..  code-block:: json

    {
        "version": 2,
        "auth": {
            "username": "Your username",
            "accesstoken": "Your access token"
        },
        "uri": "at://…"
    }

*   ``uri`` – AT URI of the post. Corresponds to the ID under which the post was
    stored when it was read.

**Response:** Single post (``SocialServerPostResponse``).

Error handling
--------------

If an error occurs on the server, you have two options:

*   You respond with a JSON that can be deserialized into a
    ``SocialServerErrorResponse``.
*   You respond with an HTTP error status (4xx) and a body in the following
    form:

    ..  code-block:: json

        {
            "error": {
                "message": "Error description",
                "code": 1652117377
            }
        }

In both cases, ``message`` and ``code`` are evaluated by the extension and
displayed in the backend as a synchronization error.

..  tip::
    If a single post was deleted in the network, return an error with the code
    ``1585047168``. The extension then marks the corresponding post as deleted
    at the origin.
