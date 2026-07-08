..  _frontend-output:

===============
Frontend Output
===============

To output the imported posts in the frontend, *fp_social* provides two of its
own plugins (content elements). Choose the plugin that suits your use case:

*   :ref:`plugin-post` – outputs a single, fixed selected post.
*   :ref:`plugin-wall` – outputs multiple posts as a social wall, which
    automatically loads new posts and optionally allows older posts to be
    loaded manually.

The display of the individual posts (e.g. truncating the description or showing
images) can be adjusted in the same way for both plugins. These settings are
described centrally under :ref:`post-display`.

..  toctree::
    :hidden:
    :maxdepth: 1
    :titlesonly:

    PluginPost
    PluginWall
    PostDisplay
