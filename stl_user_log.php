<div class="col-md-12 col-sm-12 col-xs-12 sppage">
    <div class="row">
        <p class="sp_title">
            <?php _e( 'Logs'); ?>
          <?php 
          global $wpdb;
           $table_log = $wpdb->prefix.'cfdbworkflow_logs';
           $log_datas = $wpdb->get_results("SELECT * FROM $table_log ");
           ?>
        </p>
        <div class="container-fluid">
            <div class="row ors-columns-outer">
                <div class="col-md-12">
                    <table class="wp-list-table widefat fixed striped ">
                        <thead>
                            <tr>
                                <td><?php _e( 'S.no', 'student-portal' ); ?></td>
                                <td><?php _e( 'Log Type', 'student-portal' ); ?></td>
                                <td><?php _e( 'Log Details', 'student-portal' ); ?></td>
                                <td><?php _e( 'Date & Time', 'student-portal' ); ?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if($log_datas)
                            {
                                $i = 0;
                                foreach($log_datas as $log_data)
                                {
                                
                                    $i++;                            
                                    echo "<tr><td>".$i."</td>";
                                    echo "<td>".$log_data->log_type."</td>";
                                    echo "<td>".$log_data->log_details."</td>";
                                    echo "<td>".$log_data->created_on."</td>";
                                    echo "</tr>";

                                }
                            }
                            ?>
                        </tbody>

                    </table>
                </div>
               
            </div>
        </div>

    </div>
</div>