..  _social-server-settings:

==================================
Setting the Social Server URL
==================================

You can set the URL of the Social Server in use in the extension settings under
``apiUrl``. Please make **absolutely sure** to include the trailing slash at the
end of the URL!

The default value is ``https://social.fixpunkt.com/``.

You can find the dev server at ``https://dev.social.fixpunkt.com/``.

..  note::
    If you do not wish to use our paid SocialServer, you can specify your own
    server URL here. How to provide your own Social Server is described under
    :ref:`own-social-server`.

..  _rate-limit:

Request limits of the social server
===================================

The social server limits how often its API may be called. Two limits apply, both
counted per API user:

*   **10 requests per minute to the same social media profile.** Where a request
    does not name a profile -- a single post, for instance -- exactly that
    request is limited instead.
*   **30 requests per minute** in total.

Once a limit is reached the server answers with HTTP ``429`` and states from
which point in time it can be used again. This extension does not treat that as
an error:

*   A **short** block is waited out and the request is retried once.
*   A **longer** block postpones the account. Its
    :guilabel:`Synchronization postponed until` field is filled in and the
    :ref:`scheduler task <scheduler-task>` skips the account until then. Every
    other account is synchronized as usual.

Where the boundary between "short" and "long" lies is set in the extension
settings under ``rateLimitWaitThreshold``, in seconds. The default is ``10``; a
value of ``0`` never waits and always postpones.

..  note::
    The limits live on the server, not here. Lowering
    ``rateLimitWaitThreshold`` does not reduce the number of requests, it only
    changes whether a run waits or moves on.

..  _unusable-answer:

When the answer does not fit
============================

Two problems are not down to the content of an answer but to the answer itself:
its protocol version differs from the one this extension speaks, or its type is
unknown here. Both show up as an ordinary synchronization error -- in the
:guilabel:`Last synchronization` column of the backend module, in the deep
synchronization and in the output of the :ref:`scheduler task
<scheduler-task>` -- but with a message that names the cause:

*   *"Der Social Server hat mit Protokollversion 3 geantwortet, erwartet wird
    Version 2. ..."* -- the social server and the extension
    ``fp_social_bridge`` do not match. Usually one of the two is outdated.
*   *"Die Antwort des Social Server konnte nicht gelesen werden (Typ "...")."*
    -- the social server knows a response type that ``fp_social_bridge`` does
    not know yet. An update of the extension helps here.

..  note::
    A corrupted answer -- no valid JSON at all, e.g. an error page of a proxy in
    front of the server -- is reported separately as
    :guilabel:`Received data is corrupted.`
