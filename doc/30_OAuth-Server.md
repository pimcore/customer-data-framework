# Working with OAuth

This chapter describes of how to use the OAuth - Server. To help you build login or authorization-systems 
as quickly as possible to handle a client coming from different types of devices and platforms (Web-App, mobile App).

There are several ways to authenticate and after authenticated successfully to authorize an user. 
For authentication/authorization-issues you can use grants to identify a certain user. 
Before those grants are described in detail, 
there are some technical terms that need to be described first:

- Client

  In this documentation a Client is a application on different devices like a Web-App on a PC or a mobile App on a Smartphone.
  Therefore a Client represents only the mean of how an User can be authenticated with, not an User itself.
  
- User 

  In this documentation an User is a registered Person a your platform that you want to establish a login-system for.  

- CustomerProviderInterface

  This service is used to get user-instances that you want to authenticate by using the OAuth - Server. 
  For Details see [Working with Customers](./05_Working-with-Customers.md)

- Access - Token

  After an User has been authenticated successfully, they receive an Access-Token from the OAuth-Server so that they are allowed to access certain API's, services or other resources.
  An Access-Token is a JWT value that contains some auth-information like `expires_in`, `access_token` and `refresh_token`. 
  This information is encrypted and is decrypted with the OAuth-Server. With help of a valid `access_token` an User is found.
  What you want to do with a found User or what resources you want to protected by Access-Tokens, it's up to you. You can use your own logic here. 
  
  There is a Starter-Service called `UserInfo` though, when you want to request information about an User like 
  Forename, Surname or Email-Address. 
  This service can be configured with `pimcore_customer_management_framework.oauth_server.userExporter`.

  For this service a action is made as well to make a request to. To get concrete information about an user, 
  you have to make a request to `getUserInfo` that contains the Access-Token in the Authorization Header.
  
  ```php
  /**
   * REQUEST AN SPECIFIC USER-INFO BY USING AN ACCESS-TOKEN, THE USER-INFO CAN BE CONFIGURED IN THE CONFIG.yml (pimcore_customer_management_framework.oauth_server.userExporter) FILE
   * @param Request $request
   * @Route("/userinfo", name="userinfo_path")
   * @return JSONResponse
   * @throws \Exception
   */
  public function getUserInfo(Request $request){
     ....
  }
  ```  

- Auth - Code
 
  In exchange for an Access-Token an Auth-Code is needed. 
  An Auth-Code is also a JWT value that contains some auth-information like `expire_time`, `auth_code_id`, `redirect_uri` and `client_id`. 

  Auth-Code is sent back to an user after they have been authenticated successfully. So the authentication process is over, but
  they have to get an Access-Token as well. That's why a requesting application has to remember the got Auth-Code and 
  has to sent it to the OAuth-Server in exchange for an Access-Token.


Grants:

Depending what type of application respectively Client you want to support, you can use an Auth-, an Implicit or a Password-Grant.

- Auth - Code Grant 

  When you want to establish a authentication/authorization - system for a third party (not trusted) Web-App or mobile App then you could use an Auth-Code-Grant.
  As first step you need to make a request to the controller action `formAuthorizeAuthGrantClient`:
  
  ```php
  /**
   * REQUEST A NEW AUTH-CODE BY LOGGING IN
   * @param Request $request
   * @Route("/form_auth_code", name="form_auth_code_path")
   * @return RedirectResponse
   * @throws \Exception
   */
  public function formAuthorizeAuthGrantClient(Request $request)
  {
     ....
  }
  ```  
  
  This action expects some GET-Parameter:
  
  -  client_id (must)
     
     It's a name like "myawesomeauthgrant"
     
  -  redirect_uri (must)
  
     A URI that you want a client to be redirected, like http://www.google.com
     
  -  response_type (must)
  
     This must have got the value of "code" 
  
  -  state (optional)
  
     Can be any value and stored in a user's session. 
     
  
  This action then renders a symfony-form that an User can be authenticated by. After an User authenticated successfully, they are 
  redirected to the former defined redirect_uri parameter. The redirected uri contains a Auth-Code (`code`) parameter and my be a `state` parameter:
  
  http://www.google.com?code=access_code_jwt&state=some_state_value
  
  The code parameter can then be used to request an Access-Token. For this to work, a request must be made onto `accessToken`:
  
  ```php
  /**
   * REQUEST AN ACCESS-TOKEN BY USING AN AUTH-CODE
   * @param Request $request
   * @Route("/access_token", name="access_token_path")
   * @return JsonResponse
   * @throws \Exception
   */
  public function accessToken(Request $request)
  {
     ....
  }
  ```
    
  This action expects some POST-Parameter:
      
  - code (must)
             
      It's the got JWT requested with `formAuthorizeAuthGrantClient`
         
  - client_id (must)
         
      It's a name like "myawesomeauthgrant"
         
  - client_secret (must)
          
      It's a value that must be kept secret 
                  
  - redirect_uri (must)
      
      It's the same URI again
         
  - grant_type (must)
          
      This must have got the value of "authorization_code" 
    
  If the got Auth-Code (`code`) is valid, then a JSON is returned. This JSON contains:
    
  - token_type
    
       A static value "Bearer"
    
  - expires_in
    
       A timestamp in seconds when this Access-Token expired by
         
  - access_token
    
       A JWT value that an application can use to request protected resources.
         
  - refresh_token
        
       A JWT value that an application can use to refresh an expired Access-Token.
     
    
  An application can store this JSON to use it afterwards when requesting a protected resources. 
  At this point an application is ready to request sensitive information stored on your platform. 
    
  When an Access-Token has expired an application can get an new one by making a request onto:
  
  ```php
  /**
   * REQUEST A NEW ACCESS-TOKEN BY USING A REFRESH-TOKEN
   * @param Request $request
   * @Route("/refresh_token", name="refresh_token_path")
   * @return JSONResponse
   * @throws \Exception
   */
  public function refreshToken(Request $request)
  {
     ....
  }
  ```
   
  This action expects some POST-Parameter:
        
  - refresh_token (must)
               
      A former got Refresh-Token as JWT with `accessToken`
           
  - client_id (must)
           
      It's a name like "myawesomeauthgrant"
           
  - client_secret (must)
            
      It's a value that must be kept secret 
          
  - grant_type (must)
            
      This must have got the value of "refresh_token" 
  
  If the got Refresh-Token (`refresh_token`) is valid, then a similar structured JSON is returned as with `accessToken`.


- Implicit Grant


  When you want to establish a authentication/authorization - system for a third party (not trusted) Web-App or mobile App then you could use an Implicit-Grant.
  As first step you need to make a request to the controller action `formAuthorizeImplicitGrantClient`:
  
  ```php
  /**
   * REQUEST A NEW ACCESS-TOKEN BY USING AN IMPLICIT GRANT
   * @param Request $request
   * @Route("/form_auth_implicit", name="form_auth_implicit_path")
   * @return RedirectResponse|Response
   * @throws \Exception
   */
  public function formAuthorizeImplicitGrantClient(Request $request)
  {
     ....
  }
  ```  
  This action expects some POST-Parameter:
    
  - client_id (must)
       
       It's a name like "myawesomeimplicitgrant"
       
  - redirect_uri (must)
    
       A URI that you want a client to be redirected, like http://www.google.com
       
  - response_type (must)
    
       This must have got the value of "token" 
    
  - state (optional)
    
       Can be any value and stored in a user's session. 
  
  If the got Auth-Code (`code`) is valid, then a JSON is returned. This JSON contains:
      
  - token_type
      
       A static value "Bearer"
      
  - expires_in
      
       A timestamp in seconds when this Access-Token expired by
           
  - access_token
      
       A JWT value that an application can use to request protected resources.
           
  - state
          
       It's the same value that sent in the original request. You should compare this value with the value stored in the user’s session to ensure the authorization code obtained is in response to requests made by this client rather than another client application.  


- Password Grant

  When you want to establish a authentication/authorization - system for a first party (trusted like your own) Web-App or mobile App then you could use an Implicit-Grant.
  As first step you need to make a request to the controller action `authorizePasswordGrantClient`:
  
  ```php
  /**
   * REQUEST A NEW ACCESS-TOKEN BY USING AN IMPLICIT GRANT
   * @param Request $request
   * @Route("/form_auth_password", name="form_auth_password_path")
   * @return RedirectResponse|Response|JSONResponse
   * @throws \Exception
   */
  public function authorizePasswordGrantClient(Request $request)
  {
     ....
  }
  ```  
  This action expects some POST-Parameter:
    
  - client_id (must)
       
       It's a name like "myawesomepasswordgrant"
       
  - client_secret (must)
              
       It's a value that must be kept secret 
        
  - grant_type (must)
    
       This must have got the value of "password" 
    
  - username (must)
    
       It's the username of a authenticating user
       
  - password (must)
      
       It's the password of a authenticating user
      
      
  If the got Authentication succeeded, then a JSON is returned. This JSON contains:
      
  - token_type
      
        A static value "Bearer"
      
  - expires_in
      
        A timestamp in seconds when this Access-Token expired by
           
  - access_token
      
        A JWT value that an application can use to request protected resources.
           
  - refresh_token
          
        A JWT value that an application can use to refresh an expired Access-Token.

All information described above can be found on [oauth2.thephpleague.com](https://oauth2.thephpleague.com/) as well.