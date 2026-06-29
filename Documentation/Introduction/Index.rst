..  _introduction:

============
Introduction
============

Supported networks
===================

Currently, *fp_social* supports the following social networks out of the box:

*   **Facebook**

    *   Import of posts from pages of which you are an administrator.

*   **Instagram**

    *   Your own profile
    *   Recent posts as well as top posts of a hashtag

*   **Wordpress**

    *   All entries of a blog.
    *   All entries that are tagged with a certain tag.
    *   All entries of a specific author.

*   **Youtube**

    *   Latest entries of a profile.

*   **LinkedIn**

    *   Import of posts from pages of which you are an administrator.

*   **Bluesky**

    *   Import of posts from a profile.

Data protection
===============

In order to protect the visitors of your website from tracking by social
networks, *fp_social* does not output the posts directly via the social
networks.

..  figure:: /Images/schaubild.png
    :alt: Diagram on data protection

    Diagram showing that no communication takes place between the website
    visitor and the social network.

The output of the posts works as follows:

*   Posts are synchronized into the database of your TYPO3 instance according
    to the configured accounts.
*   Data that is made available to the user is read from the TYPO3 instance
    and output.

This ensures that the social networks cannot store any cookies on the
visitors' computers and that no tracking is possible either.
