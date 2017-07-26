<?php
$total_pages = ceil( $total / $per_page );

if ( $total_pages > 1 ): ?>
    <nav>
        <ul id="amo-pagination" class="pagination">
            <?php
            if ( $current_page > 1 ) {
                $url_params['page'] = 1;
                echo '<li><a href="?' . http_build_query( $url_params ) . '">&laquo;</a></li>';
            } else {
                echo '<li class="disabled"><span>&laquo;</span></li>';
            }

            for ( $i = 1; $i <= $total_pages; $i ++ ) {
                if ( $i == 1 && $current_page == 1 ) {
                    echo '<li class="disabled"><span>&lsaquo;</span></li>';
                } else if ( $i == 1 ) {
                    $url_params['page'] = $current_page - 1;
                    echo '<li><a href="?' . http_build_query( $url_params ) . '">&lsaquo;</a></li>';
                }

                if ( $i == $current_page ) {
                    echo '<li class="active"><span>' . $i . '</span></li>';
                } else {
                    $url_params['page'] = $i;
                    echo '<li><a href="?' . http_build_query( $url_params ) . '">' . $i . '</a></li>';
                }

                if ( $i == $total_pages && $current_page == $total_pages ) {
                    echo '<li class="disabled"><span>&rsaquo;</span></li>';
                } else if ( $i == $total_pages ) {
                    $url_params['page'] = $current_page + 1;
                    echo '<li><a href="?' . http_build_query( $url_params ) . '">&rsaquo;</a></li>';
                }
            }

            if ( $current_page < $total_pages ) {
                $url_params['page'] = $total_pages;
                echo '<li><a href="?' . http_build_query( $url_params ) . '">&raquo;</a></li>';
            } else {
                echo '<li class="disabled"><span>&raquo;</span></li>';
            }
            ?>
        </ul>
    </nav>
<?php endif; ?>
