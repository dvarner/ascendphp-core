<div class="row">
    <div class="col-md-3">
        <div class="sticky-top pt-5">
            <p><a href="#intro">Intro</a></p>
            <p><a href="#features">Features</a></p>
            <p><a href="#future-features">Future Features</a></p>
            <p><a href="#respos">Repos</a></p>
            <p><a href="#min-req">Minimum Requirements</a></p>
            <p><a href="#install">Install</a></p>
            <p><a href="#file-structure">File Structure</a></p>
            <p><a href="#command-line">Command Line</a></p>
            <p><a href="#controllers">Controller</a></p>
            <p><a href="#models">Model</a></p>
            <p><a href="#views">Views</a></p>
            <p><a href="#routes">Route</a></p>
        </div>
    </div>
    <div class="col-md-9 text-left">
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="intro">Intro</a>
            </div>
            <div class="card-body">
                <p>AscendPHP Framework is about Ascending developers to the next level of development
                    by providing:
                    <ul>
                        <li>Simplified Development</li>
                        <li>Optimized Framework Speed</li>
                        <li>Separation of Framework Components</li>
                        <li>MVC Structure aka Model/View/Controller</li>
                        <li>PSR Standards</li>
                    </ul>
                </p>
                <p><span class="text-bold">Simplified Development</span> by replicating similarly liked parts of other frameworks.</p>
                <p><span class="text-bold">Optimized Framework Speed</span> by keeping code simple, clean, and focused on its purpose.</p>
                <p><span class="text-bold">Separation of Framework Components</span> by allowing developers to only have to use a small part of the framework
                    without having to use the whole.</p>
                <p><span class="text-bold">MVC Structure aka Model/View/Controller</span> by using structure to organize the code.</p>
                <p><span class="text-bold">PSR Standards</span> by giving a standard for developers to follow.</p>
                <br />
                <p>AscendPHP has been developed to provide similar simplistic development to other frameworks but
                    with more optimized code focused on only what is needed to make each part work.</p>
                <p>It also has a focus on not just building out a framework but providing tools to
                    easily develop CMS aka Content Management System through REST features, Authorization, Permissions, etc.</p>
                <p>A few key concepts in the development of AscendPHP is the want to keep all the sql logic inside the models
                    and the controllers just to be the mediator between the views and the models.
                    Also, wanted to separate the logic for services aka Third Party Services outside the controllers as well.</p>
                <p>With all this said the framework has a mysql database requirement to work because that has been what is needed
                    for each of the projects which have so far been created using it.
                    However, this does not have to be a requirement and could easily be decoupled if enough requests
                    for the use without a database are made.
                    Again, this is why it was designed how it is; to easily be separated out so it can do specifically what the developer needs.
                </p>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="features">Features</a>
            </div>
            <div class="card-body">
                <p>AscendPHP Framework has many features so here is a list:
                <ul>
                    <li>Command Line Wrapper with Interface</li>
                    <li>Session built on Database</li>
                    <li>Modules</li>
                    <li>MVC</li>
                </ul>
                </p>

                <h4>Command Line Wrapper with Interface</h4>
                <p>Easy to created command line scripts within the Command Line Wrapper feature. It also includes a command line interface. Check out Command Lines tutorial.</p>

                <h4>Session built on Database</h4>
                <p>Currently the reason the framework requires a database no matter what is sessions are stored in the database but this id done for easy management and speed.</p>

                <h4>Modules</h4>
                <p>Added as recent as Sept 1, 2019. This allowed us to move the Examples out of the AscendPHP structure code and into the AscendPHP-Core but under the namespace Examples and then within an MVC framework same as AscendPHP structure.
                    The future plans for this feature is to add another module for authentication which is already pre-built and can easily be used by developers without even writing a single line of code.
                    However, if they want to change something they can easily copy/paste it out of AscendPHP-Core and into AscendPHP structure and modify it to their needs.
                    More to come as we develop this out.</p>

                <h4>Modules</h4>
                <p>AscendPHP is built on the concept of MVC aka Model, View, Controller with additional CommandLine and Services to come sections for organization of code.
                    Read in more detail about these in thier own sections.</p>

            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="future-features">Future Features</a>
            </div>
            <div class="card-body">
                <p>AscendPHP Framework has many features built out but not implemented or that we want to expand.
                     Here are just a few of the exciting features to come:
                <ul>
                    <li>Authentication Area with Admin</li>
                    <li>Command Line Colors</li>
                    <li>Cron's in One Place</li>
                    <li>Model Chaining</li>
                    <li>REST Route</li>
                    <li>Validation</li>
                </ul>
                And many, many more as AscendPHP grows.
                </p>

                <h4>Authentication Area with Admin</h4>
                <p>Inclusion of a basic members area with register, confirmation, login, forgot password, roles, permissions, and basic admin control panel.
                    This was in our previous version and want to bring it back.</p>

                <h4>Command Line Colors</h4>
                <p>Ability to do self::success('message') and see message in green
                    or self::failed('error') to see errors in red.
                    This was implemented in the previous version and want to bring this back.</p>

                <h4>Cron's in One Place</h4>
                <p>Load one cron master php file into crontab and then it runs all your crons for you.</p>

                <h4>Model Chaining</h4>
                <p>Extend the abilities of the model chaining to multi-table inclusion and more.
                    See Model section to see our simple implentation until we expand.</p>

                <h4>REST Routes</h4>
                <p>The ability to have one Route line like below:</p>
                <p><code>Route::rest($uri, $end_point);</code></p>
                <p><code>Route::rest('/api/', 'user');</code></p>
                <p>
                    And it handle all of the following in the below format: GET (One/Many), POST, PUT, and DELETE.
                    <ul>
                        <li>GET /api/user/{id} :: Get 1 record.</li>
                        <li>GET /api/users :: Get all record.</li>
                        <li>GET /api/users?page=# :: Get all record; paginated.</li>
                        <li>GET /api/users?action=search&key=field&value=find-this :: Find only these records.</li>
                        <li>POST /api/user :: Create a record.</li>
                        <li>PUT /api/user :: Update a record by ID.</li>
                        <li>DELETE /api/user :: Soft delete a record by ID.</li>
                    </ul>
                </p>
                <p>** Please, understand this is just a rough draft of our plans and they can change.</p>
                <p>Also, included is a commandline tool which would make creating the model and class easy and fast.</p>

                <h4>Validation</h4>
                <p>Our previous framework version has an extensive list of validations and we want to bring that back.</p>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="respos">Repos</a>
            </div>
            <div class="card-body">
                <p><a href="https://packagist.org/packages/dvarner/ascendphp">https://packagist.org/packages/dvarner/ascendphp</a></p>
                <p><a href="https://github.com/dvarner/ascendphp">https://github.com/dvarner/ascendphp</a></p>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="min-req">Minimum Requirements</a>
            </div>
            <div class="card-body">
                <p>Requires:</p>
                <ul>
                    <li>MySQL 5.5+</li>
                    <li>PHP 5.6+ (Eventually PHP 7+ Only)</li>
                    <li>PHP PDO Extension</li>
                </ul>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="install">Install</a>
            </div>
            <div class="card-body">
                <p>Follow AscendPHP readme. AscendPHP-Core is the composer package which has the core files. More components, later.</p>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="file-structure">File Structure</a>
            </div>
            <div class="card-body">
                <p>Folders:</p>
                <ul>
                    <li>App - Stores specific files for this application</li>
                    <li>App/CommandLine - Custom command line classes</li>
                    <li>App/Controller - Middleman which controls data from Models to Views aka Database to Display</li>
                    <li>App/Model - Structure of tables for database and all sql calls in function form</li>
                    <li>App/View - Framework code for content to display to users and/or public</li>
                    <li>public - Files for the public to see</li>
                    <li>public/js - Javascript</li>
                    <li>public/css - CSS</li>
                    <li>public/fonts - Fonts</li>
                    <li>storage - Files to be stored</li>
                    <li>storage/cache - Cache files</li>
                    <li>storage/data - Data files</li>
                    <li>storage/log - Log files</li>
                    <li>vendor - Third parties through composer</li>
                </ul>

                <p>Files:</p>
                <ul>
                    <li>App/config.php - Configurations</li>
                    <li>App/config.sample.php - Example Configurations</li>
                    <li>App/routes.php - List of routes for applications</li>
                    <li>php ascend - Command line tools</li>
                </ul>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="command-line">Command Line</a>
            </div>
            <div class="card-body">
                <h4>How to use AscendPHP Command Line</h4>
                <p>Inside the App\CommandLine folder is where of course all the framework command line scripts will be.
                    To use the command line type the following into the terminal.</p>
                <p><code>php ascend</code></p>
                <p>The above will give you all available commands.
                     Currently only sql:migrate is available.
                     sql:migrate will take all Models and create the database for them.
                     Make sure you have setup config.php and filled out the required database files.
                </p>
                <p><code>php ascend sql:migrate</code></p>

                <h4>How to Create a Command Line Script</h4>
                <p>Below is an example of a command line script.</p>
                <p><code><?=Ascend\Core\file2code_example(PATH_PROJECT . ASCENDPHP_VENDOR_PATH . 'Ascend/Examples/App/CommandLine/ExampleCommandLine.php'); ?></code></p>
                <p>Line 1. Make sure model is in namespace App\CommandLine.</p>
                <p>Line 3. Add the use Ascend\Core\CommandLineWrapper which is what all command lines are built off of.</p>
                <p>Line 5. Declare the class, then the command line name CamelCase, then extends CommandLineWrapper.</p>
                <p>Line 7. Create a private static with name $command and write its value for the name of the command you want to create.</p>
                <p>Line 8. Create a private static with name $name and write information about it.</p>
                <p>Line 9. Create a private static with name $help and write how the command will be called and list arguments available for example.
                     These are listed when calling just "php ascend" in the command line.</p>
                <p>Line 11+. Create a public static function named run and start building out the command line script.
                     Currently, there are few builtin functions but self::out('message goes here to screen') is one.
                     The framework did have the capability to do colors within the command line from our previous version
                     but it needs to be re-implemented it.</p>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="controllers">Controller</a>
            </div>
            <div class="card-body">
                <h4>How to use AscendPHP Controller</h4>
                <p>Inside AscendPHP all functions called by the Route class are static.</p>
                <p><code><?=Ascend\Core\file2code_example(PATH_PROJECT . ASCENDPHP_VENDOR_PATH . 'Ascend/Examples/App/Controller/ExampleController.php'); ?></code></p>
                <p>Line 1. Make sure controller is in namespace App\Controller;</p>
                <p>Line 3. Do a use Ascend\Core\View; // This is used for getting view files easily and also allows passing variables.</p>
                <p>Line 5. Define class [ClassNameController]. We always append Controller and CamelCase.</p>
                <p>Line 7. Within the class naming is up to you but we choose to name things like so:</p>
                <ul>
                    <li>viewPageName(); // its a view then its name</li>
                    <li>getUser(); // to say method then whats it doing</li>
                    <li>postUser(); // to say method then whats it doing</li>
                </ul>
                <p>Now, why do we call View::html() twice.
                    The reason is the first one on line 10 is to get the page content.
                    The 2nd on line 12 is to get the template.
                    If you look it loaded line 10 into $tpl['container']
                    then we loaded $tpl into line 12 and inside the template is a variable $container.
                    You could also pass $tpl['title'] = 'name of site'; and that is a variable inside the template we made.</p>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="models">Model</a>
            </div>
            <div class="card-body">
                <p>A few things to keep in mind about our models:
                    <ul>
                        <li>They are loaded through running a command line script listed below.</li>
                        <li>They are stored in a migration table which keeps up with changes so every time a Model field change is made; run the migration.</li>
                        <li>Models should hold all sql. No sql should ever be in a controller.</li>
                    </ul>
                </p>

                <p><code>php ascend migrate</code></p>
                <p>Eventually the command rollback will be added for 1 or more.</p>
                <p><code>php ascend migrate rollback [#]</code></p>

                <h4>Required Model Features</h4>
                <ul>
                    <li>$table = Name of table</li>
                    <li>$fields = Array of fields names key and value sql type set</li>
                </ul>
                <p>Below is the skeleton of a model. Below the skeleton there are explanations per line.</p>
                <p><code><?=Ascend\Core\file2code_example(PATH_PROJECT . ASCENDPHP_VENDOR_PATH . 'Ascend/Examples/App/Model/Example.php'); ?></code></p>
                <p>Line 1. Make sure model is in namespace App\Model;</p>
                <p>Line 3. Add the use Ascend\Core\Model which is what all models are built off of.</p>
                <p>Line 5. Declare the class, then the model name CamelCase, then extends Model</p>
                <p>Line 7. Define a protected static $table variable with "table name"</p>
                <p>Line 8: Define a protected static $field variable as an array.</p>
                <p>Line 9+: List each table field as key and mysql type as value in array per line.</p>
                <p>** id is by default listed as the primary key for the table.
                    I plan to have a way to change the primary key in the future.</p>
                <p>** Also, created_at, updated_at, and deleted_at are default.ly created for every time.
                    I plan to have a way to add a variable later to disable this.</p>
                <p>That is all that is required to create a Model. The rest of the features below are optional.</p>

                <h4>Optional Model Features</h4>

                <h5>Seeding the table on first load either manually or by csv</h5>
                <p>Line 15-18: When adding $seeding = [] into the model it allows for pre-filled data to be loaded immediately after creation.
                    Every row should have the keys/values for each field in the table.</p>
                <p>Line 20-27: Because having manually loaded seed data vs loading from a csv is not allowed at the same time;
                    the data coming from a csv is commented out. However, if you have a massive amount of data you need preloaded
                    then follow these steps to load from a csv.</p>
                <p>Line 23: Start of array to make each row in csv to a field in table. 'map' is always used.</p>
                <p>Line 24: Key is the $fields[key] and value is the column in csv. Add as many as you like and not all table fields have to be filled.</p>

                <h5>Creating functions to call for getting results</h5>
                <p>Line 29: Defines how to create a static function for the Model to be called on.
                    If I were to call line 29 in a controller I would do such in example below.
                    All called functions are static functions.</p>
                <p><code>$rows = Example::getAllActive();</code></p>
                <p>Line 31: Gets the name of the table from the model you are within.</p>
                <p>There are 2 ways to write sql: raw or chaining.</p>
                <p>First, the commented out version is raw.</p>
                <p>Line 33-34: Has the raw sql and then returns many results as a multi dimensional array and the key being the primary id.</p>
                <p>Line 34: If you were to change it to return self::one($sql) it would result in a single array with key/values.</p>
                <p>Second way is to call a query through chaining.
                    Now, I will warn it it still under heavy development and is minimally built out
                    but we will be working to add more in the future for easier code-ability.</p>
                <p>Chaining will work as displayed on line 36.
                    First, call self to reference model inside or call the model by name if within a controller.
                    Next, define where and you can define as many as you like chained.
                    Then, define orderBy as displayed.</p>
                <p>And last, either call ->first() or ->all().</p>
                <p>->first() will only bring the first record in and return a key/value array.</p>
                <p>->all() will return a multi dimensional array with primary key as key and array with key/values.</p>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="views">Views</a>
            </div>
            <div class="card-body">
                <p>AscendPHP tries to keep things separate to make it easy to manage and maintain.
                    The 2 main parts to a few are the template and the page.
                    AscendPHP likes to name the template files _template.php to keep them at the top easily findable then all the pages below.
                    Within these files php is allows so instead of using a templating engine we just allow easy access for now.
                    The way variables are passed in are loaded as key/value $tpl[key] = value but they are extracted so they turn into the real variable; $key.
                    Just for now if you want examples to the App/View or vendor/dvarner then Ascend/Examples.
                    More documents as we develop this more.</p>
            </div>
        </div>
        <br />
        <div class="card">
            <div class="card-header">
                <a class="doc-header" name="routes">Routes</a>
            </div>
            <div class="card-body">
                <p>Ok, to get started with routes you must open App/routes.php.</p>
                <p>Once you have the file open, there are a few things to keep in mind.</p>
                <p>First, the two "use" at the top are required at all times to make the page work.</p>
                <p>Lets go over what those are:</p>
                <p>Route -> is used to call all your routes; pretty self explanitory.</p>
                <p>SiteLog -> logs every action on the site. As you see its a model.</p>
                <p>Next, SiteLog::insertUri() is the action which does the logging.
                    If you choose you do not want to log every action to the site then comment this out.
                    The use behind this is to build your own user stats for the site.</p>
                <p>Then, there is Route::display404().
                    This should always be at the end of the script so a 404 is displayed to the user if no pages are called.
                    So basically the way Route:: works is it goes through every one like if statements until it finds a match then executes and dies.
                </p>
                <p>So now lets look at the current route we have. It has 3 parts:</p>
                <p><code>Route::view('/','Page','viewIndex');</code></p>
                <p>The "/" which is the uri it matches aka the part after the domain.com -> /everything-that/is-here.</p>
                <p>The "Page" which is the Controller inside App/Controller folder.</p>
                <p>Then, "viewIndex" which is the static function inside the "Page" Controller.</p>
                <p>**Keep in mind all classes and functions in routes are case sensitive.</p>
                <br />
                <p>To get started, lets created a duplicate route to Controller Page and static function viewIndex.</p>
                <p><code>Route::view('/test','Page','viewIndex');</code></p>
                <p>Now type "your-domain.com/test" the above will route to the same page as the index.</p>
                <p>Lets say we want the route to return as a json.</p>
                <p><code>Route::json('/test','Page','viewIndex');</code></p>
                <p>GET Method: Just example.</p>
                <p><code>Route::get('/test','Page','getUser');</code></p>
                <p>POST Method: Just example.</p>
                <p><code>Route::post('/test','Page','postUser');</code></p>
                <p>PUT Method: Just example.</p>
                <p><code>Route::put('/test','Page','putUser');</code></p>
                <p>DELETE Method: Just example.</p>
                <p><code>Route::delete('/test','Page','deleteUser');</code></p>
                <p>REST Method: (UNDER CONSTRUCTION).</p>
                <p><code>Route::rest('/test','Page','restUser');</code></p>
            </div>
        </div>
        <br />
    </div>
</div>
