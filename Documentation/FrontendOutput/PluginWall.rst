..  _plugin-wall:

====================
Plugin "Social Wall"
====================

The ``[FpSocial] Social Wall`` plugin outputs multiple posts as a social wall.
The wall automatically loads new posts and can optionally show a button for
manually loading older posts.

*Placeholder: an example screenshot of the "Social Wall" plugin goes here.*

..  Once the screenshot is available, replace the placeholder above with a
    figure directive, e.g.:

    ..  figure:: /Images/FrontendOutput/plugin-wall.png
        :alt: Example of the "Social Wall" plugin

        The "Social Wall" plugin in the page content.

Selection of the Posts
======================

``Social Media Accounts``
    The accounts whose posts should be output.

``Fixed Records``
    Individual posts that are always shown in the wall, independently of the
    selected accounts.

``Restrict output to the following hashtags...``
    Filters the posts imported via the accounts by hashtags. Only posts that
    have at least one of the selected hashtags are shown. The filter is only
    taken into account if at least one hashtag is selected.

Number of Posts to Display
==========================

The number of posts shown initially is the product of rows and columns.

``Columns``
    **Default:** 1

    The number of columns.

``Rows``
    **Default:** 1

    The number of rows.

Loading More
============

``Automatically load new records``
    **Default:** False

    If this setting is enabled, posts that were created after the page was
    loaded are loaded automatically.

``Replace older records with newer ones``
    **Default:** False

    If this option is enabled, the oldest displayed post is removed when a new
    post is loaded automatically. This keeps the number of displayed posts the
    same and prevents the page from "jumping".

``Allow manual loading of older records``
    **Default:** False

    If this setting is enabled, a button is shown below the social wall that
    can be used to load additional, older posts. These increase the number of
    displayed posts and do not replace existing posts.

``Label of the load button``
    The label of the button used to load additional, older posts.

Display of the Posts
====================

How the individual posts are displayed can be adjusted via the settings
described under :ref:`post-display`.
