<?php
	class ContentLoader {

        private $controller;
        private $connection;

        public function __construct($controller, $connection) {
            $this->controller = $controller;
            $this->connection = $connection;
        }

        public function load() {
            
            /**
             * Page Content
             */
            $exq = $this->connection->prepare("SELECT * FROM apps");
            $exq->execute();
            $apps = $exq->fetchAll(PDO::FETCH_ASSOC);
            
            $installed_apps = array();
            $apps_rows = "";
            if (count($apps) > 0) {
                foreach($apps as $row) {
                    $app_id 		= $row["app_id"];
                    $app_installed 	= $row["app_installed"];
                    $app_json 	= ROOT_PATH . "/apps/" . $row["app_directory"] . "/app.json";
                    
                    $file = file_get_contents($app_json);
                    $json = json_decode($file, true);
                    
                    $name 			= $json["name"];
                    $version 		= $json["version"];
                    $author			= $json["author"];
                    $description	= $json["description"];
                    $run_command	= $json["author"];
                    $script			= $json["script"];
                    array_push($installed_apps, $name);

                    $apps_rows .= "
                    <tr>
                        <td>$name</td>
                        <td>$version</td>
                        <td>$author</td>
                        <td>$description</td>
                        <td>$script</td>
                        <td class=\"center\">
                            <a class=\"btn-floating tooltipped waves-effect waves-light btn-action\" data-action=\"app_run_normal\" data-app-id=\"$app_id\" data-position=\"top\" data-delay=\"50\" data-tooltip=\"Run\"><i class=\"material-icons\">play_circle_outline</i></a>
                            <a class=\"btn-floating tooltipped waves-effect waves-light orange btn-action\" data-action=\"app_run_sudo\" data-app-id=\"$app_id\" data-position=\"top\" data-delay=\"50\" data-tooltip=\"Run as root\"><i class=\"material-icons\">play_circle_filled</i></a>
                            <a class=\"btn-floating tooltipped waves-effect waves-light red btn-action\" data-action=\"app_remove\" data-app-id=\"$app_id\" data-position=\"top\" data-delay=\"50\" data-tooltip=\"Uninstall\"><i class=\"material-icons\">delete</i></a>
                        </td>
                    </tr>";
                }
            } else {
                $apps_rows .= "<tr><td>N/A</td><td>N/A</td><td>N/A</td><td>N/A</td><td>N/A</td><td>N/A</td></tr>";
            }
            
            $this->controller->set("apps", $apps_rows);
            $this->controller->set("output", "<pre>Run an application to get an output...</pre>");
            
            $repo_apps_rows = "";
            if(ENABLE_REPOS) {
                try {
                    $repo_apps_json = @file_get_contents(APPS_REPO . "/applications.json");

                    if ($repo_apps_json) {
                        $repo_apps = json_decode($repo_apps_json, true);
                        $app_count = 0;
                        foreach ($repo_apps as $app) {	
                            $name 			= $app["name"];		
                            $version 		= $app["version"];
                            $author 		= $app["author"];
                            $last_update 	= $app["last_update"];
                            $description 	= $app["description"];
                            $file 			= $app["file"];
                            $app_url		= APPS_REPO . "/$file";

                            if(!in_array($name, $installed_apps)) {
                                $app_count++;
                                $repo_apps_rows .= "
                                        <tr>
                                            <td>$name</td>
                                            <td>$version</td>
                                            <td>$author</td>
                                            <td>$last_update</td>
                                            <td>$description</td>
                                            <td class=\"center\">
                                                <a class=\"btn-floating tooltipped waves-effect waves-light btn-action\" data-action=\"app_download\" data-app-url=\"$app_url\" data-position=\"top\" data-delay=\"50\" data-tooltip=\"Install App\"><i class=\"material-icons\">get_app</i></a>
                                            </td>
                                        </tr>";
                            }
                            
                        }
                        if($app_count == 0)
                            $repo_apps_rows .= "<tr><td>N/A</td><td>N/A</td><td>N/A</td><td>N/A</td><td>N/A</td></tr>";
                    }
                } catch (Exception $e) {
                    //TODO: make this thing cooler 
                    echo "Repo not available :(";
                }
            } else {
                $repo_apps_rows .= "<tr><td>N/A</td><td>N/A</td><td>N/A</td><td>N/A</td><td>N/A</td></tr>";
            }
            
            $this->controller->set("apps_repo", $repo_apps_rows);
            
            //Load app logs
            //Load app logs
            $logs = "";
            $log_dir = "/var/hackox/logs/apps";
            $log_files = glob("$log_dir/*.{log}", GLOB_BRACE);
            foreach($log_files as $file) {
                $file_name = basename($file).PHP_EOL;
                $logs .= "<span class=\"title\">$file_name</span><div class=\"log-block\" data-log=\"$file\">Loading...</div>";
            }
            $this->controller->set("apps_logs", $logs);
        }
    }