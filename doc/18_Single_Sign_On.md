# Single Sign On (SSO)

The customer management framework supports integration with third-party services for single sign on. Thus, services 
like Google, Twitter, Facebook, etc. can be integrated and used as SSO providers for user registration and user login
while the CMF acts as SSO client. 

The CMF does not ship with a complete SSO client implementation but rather defines a standard setup how a SSO client 
authentication can be implemented. The basic SSO client authentication process could look like the following:

**Scenario 1: Customer is not logged in**

1) Customer clicks on a "Login with RandomProvider" button (e.g. Google). 
2)  Browser redirects to the provider, user logs in and allows our application to access its data (e.g. an OAuth grant).
3)  Browser returns the customer to our site, including details about the provider profile (e.g. a unique ID).
4) We try to find a local customer with such a provider ID ("find a local customer with the ID `12345678` on RandomProvider).      
    1) If a customer is found, he can be logged in and the process ends.
    2) If **no** customer is found, a new customer object has to be created. If additional information of the customer is 
       needed, a sign-up form can be presented with pre-filled data from the third-party response where the customer can
       check and complete the sign-up data. 
       
  
**Scenario 2: Customer is already logged in and wants to add a SSO identity to his account**

1) In a profile section, the customer has the possibility to link his profile with third-party providers, for example by 
  clicking on a "Connect with RandomProvider" button
2) After clicking on the button, we check if the identity already exists in the database (connected with another customer)
  and if not, we connect the customer with the identity. 
  

## Components Provided by the CMF

The SSO integration builds on top of [Symfony's Security Component](https://symfony.com/doc/current/security.html). Please
read the security documentation for further information.

The CMF provides two core interfaces which deal with storing and fetching SSO data on/from a customer. These interfaces can
be used in combination with Symfony's Security component and optional third party bundles (e.g. 
[HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle) for OAuth integration) to build a flexible login system.  

#### SsoIdentity

See [SsoIdentityInterface](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/SsoIdentityInterface.php).

The actual SSO identity which maps a local customer to a remote service. E.g. local customer `foo@example.com` references a login
`12345678` on Twitter. Using this info, a customer can be identified as local customer after returning from a social login.

The SSO identity can additionally contain profile data and optionally save API credentials (e.g. OAuth tokens). 


#### SsoIdentityService

See [SsoIdentityServiceInterface](https://github.com/pimcore/customer-data-framework/blob/master/src/Security/SsoIdentity/SsoIdentityServiceInterface.php).

Responsible for fetching SSO identities from a customer object and storing new SSO identities to a customer. Additionally it
provides a method to look up customers by their SSO identity (e.g. find customer with ID `12345678` on Twitter).

How the SsoIdentityService stores the actual identities on the customer object is up to the implementation - the default
service stores them as objects of the type `SsoIdentity` as child objects of the customer object.



In addition to these two components, the CMF also provides three implementations for integration of the 
[HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle) with CMF's data model. 


#### OAuthAwareUserProvider

See [OAuthAwareUserProvider](https://github.com/pimcore/customer-data-framework/blob/master/src/Security/UserProvider/OAuthAwareUserProvider.php)

The `OAuthAwareUserProvider` implements the `OAuthAwareUserInterface` defined by `HWIOAuthBundle` and is able to load
users from an OAuth grant response by querying the `SsoIdentityService`.


#### AccountConnector

See [AccountConnector](https://github.com/pimcore/customer-data-framework/blob/master/src/Security/OAuth/AccountConnector.php).

The `AccountConnector` implements the `AccountConnectorInterface` defined by `HWIOAuthBundle` and is able to map OAuth
data to a customer object. Given an OAuth grant response, it creates a `SsoIdentity` and saves it to the customer object.

#### OAuthRegistrationHandler

See [OAuthRegistrationHandler](https://github.com/pimcore/customer-data-framework/blob/master/src/Security/OAuth/OAuthRegistrationHandler.php#L34).

The `OAuthRegistrationHandler` is a utility service exposing common calls used in a registration or connect process. This
is mostly a facade proxying calls to the other CMF services, but should make implementations easier as the only needed 
service is the registration handler.




## Implementation

Currently, the CMF ships with a sample client integration for OAuth 1 and 2 by integrating 
[HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle) into CMF's data model. 
 
You can find the annotated example of a simple username/password login form with added OAuth social login functionality in
[../frontend-samples/sso_client](../frontend-samples/sso_client).

> Please note that the sample does not use any of `HWIOAuthBundle`'s routing definitions and the registration/connect functionality
> provided by the `ConnectController` is not used as the registration flow in the bundle lacks flexibility and does not work
> properly with Pimcore data objects (especially the registration handler expecting a user object to be returned from the form response).
> 
> This sample implementation implements special flows for login, registration and connect that are described 
> [here](./19_SSO_Flows.md).   


To re-implement the sample, following steps are necessary: 

1) Make sure the customer class implements all necessary interfaces. See [Installation](./02_Installation.md) for details. 
2) Activate `HIOWOauthBundle` and `HttplugBundle` in your `App.php`. 
3) Activate the SSO in CMF configuration. See [Configuration](./03_Configuration.md) for details. 
4) Configure your firewalls in symfony security configuration. See also the 
   [configuration of the sample](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Resources/config/pimcore/security.yml#L9).
5) Configure the HIOWOauthBundle in symfony configuration. See the [example](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Resources/config/pimcore/config.yml#L9) 
   and the [documentation of HIOWOauthBundle](https://github.com/hwi/HWIOAuthBundle/blob/master/Resources/doc/index.md).  
6) Add routes for [login](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Controller/AuthController.php#L54) (need to have a controller action somewhere) and 
   [logout](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Resources/config/pimcore/routing.yml#L5) (no action needed, will be covered with event listener).
    
7) Build [Controller](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Controller/AuthController.php#L62) 
   and [Views](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/app/Resources/views/Auth/login.html.php) 
   for login. 

8) Build some [protected page](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Controller/ContentController.php#L38).

9) Build [Controller](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Controller/AuthController.php#L123) 
   and [Views](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/app/Resources/views/Auth/register.html.php)
   for register.

10) Add route for [oauth login](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Resources/config/pimcore/routing.yml#L8). 
    and make sure the 'Login with Adapter' [buttons link to that route](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/app/Resources/views/Auth/partials/social-login-buttons.html.php#L12-L11). 

11) Setup `resource_owners` with all needed information (`id`, `secret`) in 
   [HIOWOauthBundle config](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Resources/config/pimcore/config.yml#L14) 
   in [firewall config](https://github.com/pimcore/customer-data-framework/blob/master/frontend-samples/sso_client/src/AppBundle/Resources/config/pimcore/security.yml#L33)
   and in [parameters file]. 
    
12) Setup encryption secret in CMF configuration. See [Configuration](./03_Configuration.md) for details.      
    
13) Optional for e-commerce: Add custom authentication listener for login and logout to update e-commerce framework environment 
   (currently active user). 
   
   
