..  _own-templates:

================
Custom templates
================

Custom templates can be defined via TypoScript as usual.

..  code-block:: typoscript

    # Plugin configuration
    plugin.tx_fpsocial {
        view {
            templateRootPaths {
                0 = {$plugin.tx_fpsocial.view.templateRootPath}
                1 = EXT:fpsocial_extended/Resources/Private/Templates
            }
            partialRootPaths {
                0 = {$plugin.tx_fpsocial.view.partialRootPath}
                1 = EXT:fpsocial_extended/Resources/Private/Partials
            }
            layoutRootPaths {
                0 = {$plugin.tx_fpsocial.view.layoutRootPath}
                1 = EXT:fpsocial_extended/Resources/Private/Layouts
            }
        }
    }

Outputting information about the social network
===============================================

In the partials used to render a post (e. g. ``Post/Show``,
``Post/Compact``, ``Post/Extended`` as well as their components under
``Post/Parts``), the associated account is available as the variable
``{account}`` alongside the post (``{post}``). This object can be used to
output information about the social network – for example in the footer of a
post (``Post/Parts/Bottom``).

The following properties are available on ``{account}``:

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   -   Property
        -   Description
    *   -   ``{account.description}``
        -   Display name of the network, e. g. ``Facebook``, ``Instagram``,
            ``Youtube``, ``Wordpress``, ``Bluesky`` or ``LinkedIn``.
    *   -   ``{account.network}``
        -   Internal identifier of the network.
    *   -   ``{account.icon}``
        -   Font Awesome icon class of the network, e. g. ``fa-facebook-f``.
            Usually used together with the base class ``fab``.
    *   -   ``{account.label}``
        -   Label of the account stored in the backend (e. g. the
            displayed name of the profile).
    *   -   ``{account.channel}``
        -   Identifier of the channel or profile (e. g. the username).
    *   -   ``{account.channelUri}``
        -   Full URL to the profile or channel in the respective network.
    *   -   ``{account.channelLink}``
        -   Ready-made HTML link (``<a>`` tag) to the profile. Since HTML is
            returned here, the output must be rendered with ``<f:format.raw>``.
    *   -   ``{account.partialFolder}``
        -   Name of the network-specific partial folder. In the
            default templates it is also output as a CSS class on the post.

Example
-------

The following example shows how the footer of a post can be extended with the
name of the network, its icon and a link to the profile (analogous to the
bundled partial ``Post/Parts/Bottom``):

..  code-block:: html

    <div class="socialhint">
        <i class="fab fa-fw {account.icon}"></i>
        <span>via {account.description}</span>
        <f:format.raw>{account.channelLink}</f:format.raw>
    </div>

If you would like to build the link yourself instead of using the ready-made
``{account.channelLink}``, you can access the individual properties:

..  code-block:: html

    <a href="{account.channelUri}" target="_blank">@{account.label}</a>
