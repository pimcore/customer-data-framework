# Flows for the Single Sign On Sample Implementation 

The sample SSO implementation does not use any of `HWIOAuthBundle`'s routing definitions and the registration/connect 
functionality provided by the `ConnectController` is not used as the registration flow in the bundle lacks flexibility 
and does not work properly with Pimcore data objects (especially the registration handler expecting a user object to 
be returned from the form response).

Following you find the three flows the implementation follows for login, registration and connect. 

## Login Flow

1) A user is not logged in and is redirected to the login page on `/auth/login`.

2) On the login page, the user can 
    1) authenticate via form login
    2) via social login (e.g. Twitter, Google) or 
    3) can continue to a registration action on `/auth/register`.
   
3) If he submits the login form, the normal Symfony `form_login` authenticator does its work and tries to authenticate
   the user by fetching a user by username from the user provider and matching the given password with the password encoder.
   
4) For authentication via social login the user ... 
    1) clicks on one of the social login buttons, the link points to `/auth/oauth/login/google` (or `twitter`), depending
       on the link he clicked. 
    2) This route points to `HWIOAuthBundle:Connect:redirectToService` which starts the OAuth flow, builds an authorization 
       URL for the given service and redirects the user to the provider/resource owner (e.g. Google). 
    3) There the user can log in with his credentials (e.g. Google login) and grant access to our application.
    4) The provider returns the user to our site by redirecting to the URL given in the `oauth.resource_owners` configuration 
       section inside the firewall (see sample). In our case this is `/auth/oauth/check/google`. 
    5) This is a virtual URL not pointing to any controller - instead the `HWIOAuthBundle` configured its `OAuthListener` 
       so listen on this path for incoming requests.
    6) The `OAuthListener` transforms the request into a `UserResponseInterface` containing the OAuth response and various other
       information fetched from the provider and passes this response to the configured user provider implementing 
      `OAuthAwareUserProviderInterface`.
    7) The user provider will try to fetch a user from the given response. In our case, it will query the `SsoIdentityService`
       for a customer with the given user id (e.g. the account ID on google) and the name of the resource owner (e.g. Google).
        1) If a user is found, the user provider will return the user object and this user object is used as security token which
           defines a logged in state on the firewall. The authentication system will redirect to the success URL and the login 
           is complete.
        2) If no user is found, the user provider will throw an `AccountNotLinkedException` and redirect to the failure URL, 
           which in our case is the login page. This exception contains the OAuth access token fetched before and allows us 
           to re-fetch a `UserResponseInterface` containing the remote profile data and start a registration flow. 
           Implicitly, symfony's security system will store the exception in the session to be 
           retrieved in further requests (this is not OAuth specific but also done when a `form_login` request yields an 
           error to show an error message when re-rendering the form).  


## Registration Flow
   
After the login flow threw an `AccountNotLinkedException`, we can use the exception to link the granted OAuth account
during registration (see `AuthController` in the sample for details) to the new registered user:

The user is redirected to `/auth/login` as it is configured as `failure_path` which should be used in case of authentication 
exceptions. The login page fetches and deletes the last authentication error from the session if the error is ... 
1) ... no `AccountNotLinkedException` it just proceeds rendering the form, shows the error message as alert and the flow ends here.
2) If the error is an `AccountNotLinkedException`, ...
    1) it generates a random ID (a UUID in our case) and uses the `OAuthRegistrationHandler` to store the OAuth token in 
       the session for that given ID. 
    2) Afterwards it redirects to the registration page and passes the ID as parameters. We're now on something like 
       `/auth/register/f717b8c2-f95f-4cdb-ab84-b3c5882984e6`. The ID logic is not strictly needed (we could also just 
       save the OAuth token to the session), but this variant makes sure we really deal with the account we just granted. 
       See `OAuthRegistrationHandler` for details - you can just use your own implementation, handling it the way you need it.
    3) The registration page checks if there's a key in the request and tries to load the token from the session. If a token 
       is found and matches the expiration/timestamp constraints, the token is used to fetch user profile data from the resource
       owner (e.g. Google). This data is used to prepopulate the registration form (e.g. prefill the e-mail field with the e-mail
       provided in the OAuth profile).
    4) When the form is submitted and valid, the submission is handled by the same registration action as the one rendering the
       form. 
        1) If the form is valid, the customer object created before will be saved. 
        2) If a token was found, the `OAuthRegistrationHandler` will delegate connecting the OAuth profile to the customer object. 
           In this process the registration handler uses the `AccountConnector` to apply profile data (e.g. birthday) to 
           the user object and to create a `SsoIdentity` containing all the needed data to re-identify the user on 
           subsequent logins. After this step the customer object is created and has the OAuth profile data and the `SsoIdentity` 
           linked to the account. 
    5) As last step, the user will be logged in by creating a security token and saving it on symfony's token storage. After that
       he will be redirected to the final URL `/secure` in our case. This step is optional and heavily depends on your use case. You could also flag the user as newly registered and send him an confirmation email to activate the account.


## Connect Flow
   
When a logged in user wants to connect its account to an additional OAuth provider, the connect flow looks like the following:

1) The user clicks a "Connect to Twitter" button on his profile page (the user is logged in).
2) The button points to `/auth/oauth/connect/twitter` which is a route to the `oAuthConnectAction` on our `AuthController`. 
3) The connect action uses the `OAuthRegistrationHandler` to generate an authorization URL and redirects the user to the
   resource owner (Twitter) where he can log in and give access to his account.
4) The resource owner redirects to the `/auth/oauth/connect/twitter` action (as it was specified in the authorization URL).
   The action now uses the resource owner to fetch a `UserResponseInterface` with the access token returned from twitter.
5) If a OAuth response can be fetched, an `SsoIdentity` is created and linked to the user object. Again, the `AccountConnector` 
   is used to generate the identity and the `SsoIdentityService` is used to save it to
   the user object. The `OAuthRegistrationHandler` provides a nice facade method to handle all those steps in one method
   call.
6) After linking the `SsoIdentity` the user is redirected back to the user profile.

