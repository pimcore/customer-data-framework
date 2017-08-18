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
  application and sign-up process. Example: a Twitter OAuth grant may give access to different user details, but doesn't
  include an email address. Your sign-up form could request the email address before proceeding with registration.
  
**Scenario 2: Customer is already logged in and wants to add a SSO identity to his account**

* In a profile section, the customer has the possibility to link his profile with third-party providers, for example by 
  clicking on a "Connect with RandomProvider" button
* After clicking on the button, the process is the same as above. Instead of looking up a customer by his identity we save
  the fetched identity to the already logged in customer (after checking the identity doesn't yet exist in the database).

### Components

The SSO integration builds on top of [Symfony's Security Component](https://symfony.com/doc/current/security.html). Please
read the security documentation for further information.

The CMF provides 2 core interfaces which deal with storing and fetching SSO data on/from a customer. These interfaces can
be used in combination with Symfony's Security component and optional third party bundles (e.g. [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle)
for OAuth integration) to build a flexible login system.  

#### SsoIdentity

See [SsoIdentityInterface](../models/CustomerManagementFramework/Model/SsoIdentityInterface.php).

The actual SSO identity which maps a local customer to a remote service. E.g. local customer `foo@example.com` references a login
`12345678` on Twitter. Using this info, a customer can be identified as local customer after returning from a social login.

The SSO identity can additionally contain profile data and optionally save API credentials (e.g. OAuth tokens). 


#### SsoIdentityService

See [SsoIdentityServiceInterface](../lib/CustomerManagementFramework/Authentication/SsoIdentity/SsoIdentityServiceInterface.php).

Responsible for fetching SSO identities from a customer object and storing new SSO identities to a customer. Additionally it
provides a method to look up customers by their SSO identity (e.g. find customer with ID `12345678` on Twitter).

How the SsoIdentityService stores the actual identities on the customer object is up to the implementation - the default
service stores them as objects as childs of the customer object.


### Implementations

#### OAuth

Currently, the CMF ships with a sample client integration for OAuth 1 and 2 by integrating [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle)
into CMF's data model. This integration consists of the following parts:

* The `OAuthAwareUserProvider` implements the `OAuthAwareUserInterface` defined by `HWIOAuthBundle` and is able to load
  users from an OAuth grant response by querying the `SsoIdentityService`.
* The `AccountConnector` implements the `AccountConnectorInterface` defined by `HWIOAuthBundle` and is able to map OAuth
  data to a customer object. Given an OAuth grant response, it creates a `SsoIdentity` and saves it to the customer object.
* The `OAuthRegistrationHandler` is a utility service exposing common calls used in a registration or connect process. This
  is mostly a facade proxying calls to the other CMF services, but should make implementations easier as the only needed 
  service is the registration handler.
  
You can find an annotated example of a simple username/password login form with added OAuth social login functionality in
[../frontend-samples/sso_client_pimcore5](../frontend-samples/sso_client_pimcore5).

Please note that the sample does not use any of `HWIOAuthBundle`'s routing definitions and the registration/connect functionality
provided by the `ConnectController` is not used as the registration flow in the bundle lacks flexibility and does not work
properly with Pimcore objects (especially the registration handler expecting a user object to be returned from the form response).


##### Login Flow

Instead, the login flow works as follows:

1) A user is not logged in and is redirected to the login page on `/auth/login`
2) On the login page, the user can decide if he wants to authenticate via form login or via social login (e.g. Twitter, Google).
   Optionally, he can continue to a registration action on `/auth/register` where he can register an account.
3) If he submits the login form, the normal Symfony `form_login` authenticator does its work and tries to authenticate
   the user by fetching a user by username from the user provider and matching the given password with the password encoder.
4) If the user clicks on one of the social login buttons, the link points to `/auth/oauth/login/google` (or `twitter`), depending
   on the link he clicked. This route points to `HWIOAuthBundle:Connect:redirectToService` which starts the OAuth flow,
   builds an authorization URL for the given service and redirects the user to the provider/resource owner (e.g. Google)
   where he can log in with his Google credentials and grant access to our application.
5) After a successful grant, the provider returns the user to our site by redirecting to the URL given in the `oauth.resource_owners`
   configuration section inside the firewall (see sample). In our case this is `/auth/oauth/check/google`. This is a virtual
   URL not pointing to any controller - instead the `HWIOAuthBundle` configured its `OAuthListener` so listen on this path
   for incoming requests.
6) The `OAuthListener` transforms the request into a `UserResponseInterface` containing the OAuth response and various other
   information fetched from the provider and passes this response to the configured user provider implementing `OAuthAwareUserProviderInterface`.
7) The user provider will try to fetch a user from the given response. In our case, it will query the `SsoIdentityService`
   for a customer with the given user id (e.g. the account ID on google) and the name of the resource owner (e.g. Google).
8) If a user is found, the user provider will return the user object and this user object is used as security token which
   defines a logged in state on the firewall. The authentication system will redirect to the success URL and the login 
   is complete.
9) If no user is found, the user provider will throw an `AccountNotLinkedException` and redirect to the failure URL, which
   in our case is the login page. This exception contains the OAuth access token fetched before and allows us to re-fetch a
   `UserResponseInterface` containing the remote profile data. The login flow ends here. Implicitely, Symfony's security
   system will store the exception in the session to be retrieved in further requests (this is not OAuth specific but also
   done when a `form_login` request yields an error to show an error message when re-rendering the form).  


##### Registration Flow
   
After the login flow threw an `AccountNotLinkedException`, we make can use the exception to link the granted OAuth account
during registration (see `AuthController` in the sample for details):

1) The user is redirected to `/auth/login` as it is configured as `failure_path` which should be used in case of authentication
   exceptions.
2) The login page fetches and deletes the last authentication error from the session. If the error is no `AccountNotLinkedException`
   it just proceeds rendering the form and shows the error message as alert.
3) If the error is an `AccountNotLinkedException`, it generates a random ID (a UUID in our case) and uses the `OAuthRegistrationHandler`
   to store the OAuth token in the session for that given ID. Afterwards it redirects to the registration page and passes
   the ID as paramters. We're now on something like `/auth/register/f717b8c2-f95f-4cdb-ab84-b3c5882984e6`. The ID logic
   is not strictly needed (we could also just save the OAuth token to the session), but this variant makes sure we really 
   deal with the account we just granted. The `HWIOAuthBundle` does it in a similar way, but uses timestamps instead of UUIDs.
   We save a timestamp with the token and can optionally just handle tokens which were granted in the last x minutes. See
   `OAuthRegistrationHandler` for details - you can just use your own implementation, handling it the way you need it.
4) The registration page checks if there's a key in the request and tries to load the token from the session. If a token 
   is found and matches the expiration/timestamp constraints, the token is used to fetch user profile data from the resource
   owner (e.g. Google). This data is used to prepopulate the registration form (e.g. prefill the e-mail field with the e-mail
   provided in the OAuth profile).
5) When the form is submitted and valid, the submission is handled by the same registration action as the one rendering the
   form. Again the token is fetched from the session. If the form is valid, the customer object created before is saved. If
   a token was found, the `OAuthRegistrationHandler` will delegate connecting the OAuth profile to the user object. In this
   process the registration handler uses the `AccountConnector` to apply profile data (e.g. birthday) to the customer object
   and to create a `SsoIdentity` containing all the needed data to re-identify the user on subsequent logins. The `AccountConnector`
   will use the `SsoIdentityService` for everything related to saving an identity to the customer object. After this step
   the customer is created and has the OAuth profile data and the `SsoIdentity` linked to the account. 
6) As last step, the customer will be logged in by creating a security token and saving it on Symfony's token storage. After that
   he will be redirected to the final URL `/secure` in our case. This step is optional and heavily depends on your use case
   you could also flag the user as newly registered and send him an confirmation email to activate the account.


##### Connect Flow
   
When a logged in user wants to connect its account to an additional OAuth provider, the connect flow looks like the following:

1) The user clicks a "Connect to Twitter" button on his profile page (the user is logged in).
2) The button points to `/auth/oauth/connect/twitter` which is a route to the `oAuthConnectAction` on our `AuthController`. 
3) The connect action uses the `OAuthRegistrationHandler` to generate an authorization URL and redirects the user to the
   resource owner (Twitter) where he can log in and give access to his account.
4) The resource owner redirects to the `/auth/oauth/connect/twitter` action (as it was specified in the authorization URL).
   The action now uses the resource owner to fetch a `UserResponseInterface` with the access token returned from twitter.
5) If a OAuth response can be fetched, an `SsoIdentity` is created and linked to the user object (see step 5 in the registration
   flow). Again, the `AccountConnector` is used to generate the identity and the `SsoIdentityService` is used to save it to
   the user object. The `OAuthRegistrationHandler` provides a nice facade method to handle all thos steps in one method
   call.
6) After linking the `SsoIdentity` the user is redirected back to the user profile.


## SSO Provider

TBD
