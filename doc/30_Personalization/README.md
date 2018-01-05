# Integration with Pimcore Targeting Engine

The CMF is tightly integrated into the Pimcore personalization feature and connects the personalization for anonymous 
users with the known users managed in CMF. This is provided by: 
* Connection between Customer Segments and Pimcore Target Groups
* Adding additional conditions and actions to Pimcore Global Targeting Rules
* Special triggers, conditions and actions for Targeting in CMF Action Trigger Service 


## Pimcore Target Groups and Customer Segments 

First of all one conceptual aspect has to be clarified: 
In Pimcore there is the concept of Target Groups. Target groups are assigned to users based on their behavior only and 
are not stored anywhere. Thus target groups sum up users with similar behaviour and are the entity to personalize 
content in Pimcore documents for. Normally there is just a hand full of target groups since for each target group content
needs to be provided. 
For details see the [Pimcore docs](https://pimcore.com/docs/5.1.x/User_Documentation/Targeting_and_Personalization/Concepts.html).

The CMF introduces the concept of Segments. Segments describe a certain characteristic of an user (like age group, premium 
customer, located in France, skier, biker, etc.) and are more specific and more fine grained than target groups. They can 
be assigned to users based on various different reasons (like behaviour, customer data, manually, external data sources), 
are stored in the user profile und used to filter and segment users.
 
Thus a target group can correlate with a segment or can be a summary of multiple segments. But there will also be segments 
that are not connected to target groups at all.  

There are use cases where it makes sense to directly connect segments and target groups with each other. To do so, activate 
the checkbox `Use As Target Group` in segment objects. When doing so, a target group with the same name is automatically 
created and linked to this segment. 

![Use As Target Group](../img/use-as-target-group.jpg)


See following sections to see how assignment of target groups based on segments and vice versa can be configured.

> In a nutshell: Target groups sum up similar users on a generic level. To personalize Pimcore document content, use target groups.      
> Segments are used to store specific characteristics of an user in its user profile and can, but don't need to, be connected
> to target groups.


## Example Usecases

The the [Example Usecases Page](./05_Example_Usecases.md) for a list of typical usecases that can be implemented with 
CMF in combination with Pimcore targeting engine.  


## Detail Description of Additional Functionality

See the following sub chapters for detail description for relevant additional functionality of Pimcore targeting engine
and CMF Action Trigger Service. 

* [Additional Targeting Rule Compontents](./01_Additional_Targeting_Role_Components.md)
* [Additional Action Trigger Service Components](./03_Additional_Action_Trigger_Service_Components.md)
