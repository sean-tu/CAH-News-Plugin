# CAH-News-Plugin
Wordpress plugin for the UCF College of Arts and Humanities. CAH contains many departments, each with its own Wordpress instance in the multisite. To simplify the process of adding news posts, a central site was created, from which the individual department sites can pull content. 

* This plugin creates a 'Department' taxonomy. When publishing a news story on the main site, multiple departments can be selected on which to display the article.
* The departments that are displayed on a site can be set in the Tools menu on the dashboard. 
* A department taxonomy can be applied to all of the news posts without a set department in the same menu. 

## Usage
The **[cah-news]** shortcode can be used to display a paginated list of news posts from the main site. Only news posts with the department taxonomies set in Tools>CAH News admin page are displayed.
