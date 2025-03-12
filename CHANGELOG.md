#### v4.2.0
- Bumped minimum requirement of `pimcore/pimcore` to `^11.2`. Replaced all `$request->get()` with their explicit input source.
#### v4.1.2
Removed the package "rybakit/twig-deferred-extension". If you extend the twig layout from the Customer Data Framework, please check if custom CSS/JS code added by pimcore_head_script and pimcore_head_link is still working.

#### v4.0.0
 - Added primary key to `plugin_cmf_deletions` table.
 - Removed the package "hwi/oauth-bundle".
 - Removed the support of  Single Sign On (SSO) implementation.
 - Removed the config `pimcore_customer_management_framework.oauth_client`.

##### v3.4
- The Single Sign On (SSO) functionality is deprecated and will be removed in version 4.

#### v3.3.0
- Deprecated `DefaultCustomerProvider::getParentParentPath()`. Use  `getParentPath()` instead.
