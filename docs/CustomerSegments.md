# Customer Segments

*Customer segmentation is the practice of dividing a customer base into groups of individuals that are similar in specific ways relevant to marketing, such as age, gender, interests and spending habits.*

The customer management framework includes tools for creating and managing customer segments and segment groups. Customer segments (and groups) are regular pimcore objects.

## Manual vs. calculated segments

The customer object contains two separate fields for manual and calculated segments. Manuals segments are added within the Pimcore backend by drag and drop. Calculated segments could be added by the CMF within the segment building process.

![manual vs calculated segments](./img/Segments.png)

## SegmentManager

The SegmentManager is responsible for managing, creating, reading CustomerSegments and CustomerSegmentGroups within the CMF. Take a look at the SegmentManagerInterface there you will find inline PHP docs for each contained method.

