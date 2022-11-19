<p>Addons filtern:</p>
<div class="input-group">
    <span class="input-group-addon"><i class="fa fa-search"></i></span>
    <input id="filterInput" type="text" class="form-control">
    <span class="input-group-btn"><button id="bootstrapper_clear_input_filter" class="btn btn-default" type="button"><i class="fa fa-times"></i></button></span>
</div>
<hr>
<form method="POST">
    <table class="table table-striped table-hover" id="items">
        <tr>
            <th>
                Installieren
            </th>
            <th>
                Key
            </th>
            <th>
                Name / Autor
            </th>
            <th>
                Veröffentlicht
            </th>
            <th>
                Beschreibung
            </th>
            <th>
                Status
            </th>
        </tr>

        <?php
        foreach ($this->packagesFromInstaller as $key => $addon) {
            $published = DateTime::createFromFormat('Y-m-d H:i:s', $addon['updated']);

            $p = rex_package::get($addon['name']);

            echo '<tr>';
            if (!$p->isAvailable()) {
                echo '<td><input style="margin-right: 10px" type="checkbox" id="table-item-' . $key . '" name="packages[]" value="' . $key . '"></td>';
                echo '<td><label for="table-item-' . $key . '">' . $key . '</label></td>';
                echo '<td><b>' . $addon['name'] . ' </b><br> ' . $addon['author'] . '</td>';
                echo '<td>' . $published->format('d.m.Y') . '</td>';
                echo '<td>' . $addon['shortdescription'] . '</td>';
                echo '<td><i class="fa fa-times text-danger fa-2x" aria-hidden="true"></i></td>';
            } else {
                echo '<td><input disabled style="margin-right: 10px" type="checkbox" id="table-item-' . $key . '" name="packages[]" value="' . $key . '"></td>';
                echo '<td><label for="table-item-' . $key . '">' . $key . '</label></td>';
                echo '<td><b>' . $addon['name'] . ' </b><br> ' . $addon['author'] . '</td>';
                echo '<td>' . $published->format('d.m.Y') . '</td>';
                echo '<td>' . $addon['shortdescription'] . '</td>';
                echo '<td><i class="fa fa-check text-success fa-2x" aria-hidden="true"></i></td>';
            }

            echo '</tr>';
        }

        ?>

    </table>
    <button type="submit" class="btn btn-primary">Installieren</button>
</form>