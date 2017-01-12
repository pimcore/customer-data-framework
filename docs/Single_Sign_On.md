# Single Sign On (SSO)

The customer management framework supports integration with third-party services via single sign on in both directions:

* as SSO client, allowing customers to log in through third-party services (e.g. social logins)
* as SSO provider, acting as authentification server for other services (planned for the future)
 
## SSO Client

The CMF does not ship with a complete SSO client implementation but rather defines a standard setup how a SSO client authentification
can be implemented. The basic SSO client authentication process could look like the following:

**Scenario 1: Customer is not logged in**

* Customer clicks on a "Login with RandomProvider" button
* Browser redirects to the provider where the customer logs in with his credentials and allows our application to access its
  data (e.g. an OAuth grant).
* Browser returns the customer to our site, including details about the provider profile (e.g. a unique ID)
* We try to find a local customer with such a provider ID ("find a local customer with the ID `12345678` on RandomProvider). This 
  will only succeed if the customer logged in before.
* If a customer is found, he can be logged in and the process ends.
* If no customer is found, a new customer object has to be created. Typically, not all data needed for a customer will be
  included in the provider response, so you'll need additional data for the customer object before being able to create the
  local object. A sign-up form can be presented with pre-filled data from the third-party response where the customer can
  check and complete the sign-up data. How this is done is not provided by the framework as it is very specific to your 
  application and sign-up process.
  
**Scenario 2: Customer is already logged in and wants to add a SSO identity to his account**

* In a profile section, the customer has the possibility to link his profile with third-party providers, for example by 
  clicking on a "Connect with RandomProvider" button
* After clicking on the button, the process is the same as above. Instead of looking up a customer by his identity we save
  the fetched identity to the already logged in customer (after checking the identity doesn't yet exist in the database).

The SSO client layout consists of 3 core interfaces:


### SsoIdentity 

See [SsoIdentityInterface](../models/CustomerManagementFramework/Model/SsoIdentityInterface.php).

The actual SSO identity which maps a local customer to a remote service. E.g. local customer `foo@example.com` references a login
`12345678` on Twitter. Using this info, a customer can be identified as local customer after returning from a social login.

The SSO identity can additionally contain profile data and optionally save API credentials (e.g. OAuth tokens). 


### SsoIdentityService

See [SsoIdentityServiceInterface](../lib/CustomerManagementFramework/Authentication/SsoIdentity/SsoIdentityServiceInterface.php).

Responsible for fetching SSO identities from a customer object and storing new SSO identities to a customer. Additionally it
provides a method to look up customers by their SSO identity (e.g. find customer with ID `12345678` on Twitter).

How the SsoIdentityService stores the actual identities on the customer object is up to the implementation - the default
service stores them as objects as childs of the customer object.


### ExternalAuthHandler

See [ExternalAuthHandlerInterface](../lib/CustomerManagementFramework/Authentication/Sso/ExternalAuthHandlerInterface.php).

Responsible for the actual third-party authentication. As this can vary by authentication method and used libraries, this
interface defines 3 basic methods which define the authentication flow. All 3 methods take the request object as parameter
and are free to decide how to deal with request data:

* `authenticate()` initiates a third-party authentication (e.g. initialize state/session and redirect to third party)
* `getCustomerFromAuthResponse()` handles an auth response after returning from provider
* `updateCustomerFromAuthResponse()` uses the `SsoIdentityService` to save the `SsoIdentity` to the customer object

The CMF ships with a default external auth handler implementation which is based on the [`HybridAuth`](http://hybridauth.github.io/)
library which is already integrated into Pimcore. After configuring your available providers in `website/config/hybridauth.php`
the handler is ready and can be initialized by calling `authenticate()` on a controller action.


## SSO Provider

TBD
