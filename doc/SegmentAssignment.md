# Assigning segments to Pimcore elements

Customer segments can be associated with documents, 
assets and objects so that an interaction with those elements creates a 
connection between the customer and the segment.

## Inheritance

Assignments of segments are inherited along the respective element's tree 
so they can be conveniently set for whole groups of elements.

![segment assignment tab](./img/segmentAssignmentTab.png)

However, this might not always be intended.

### Breaking the chain of inheritance

Using the checkbox, you can disable inheritance for any node in the tree and, by extension, 
remove implicitly assigned segments of parents from that node's children.

![segment assignment tab with checkbox](./img/segmentAssignmentTabWithCheckbox.png)

## Configuration

The assignment of segments can be configured to better suit your needs.

### Allowed element types

The types of elements segments can be assigned to are specified within the symfony configuration.
The structure can be seen in the example below:

```yml
pimcore_customer_management_framework:
    segment_assignment_classes:
      types:
        document:
          page: true
          email: true
        asset:
          image: true
        object:
          object:
            Product: true
            ShopCategory: true
          folder: true
```

Below `types`, every Pimcore element type has its own sub tree wherein each valid sub type 
is represented as a boolean value with the exception of `object`. 
For those, explicit class names must be specified.  
All types default to `false` so only those required need to be configured.

## Indexing

As an effort towards scalable performance, assigned segments, both inherited and directly assigned,
are indexed in a separate table as a simple id mapping.  
Whenever an element's assigned segments are saved using `SegmentAssignerInterface`, 
that element and its children are queued to be indexed by `IndexerInterface` which can be called via 
the `cmf:maintenance` cli command (e.g. as a cronjob).

## Retrieving assigned segments

To retrieve segments assigned to 