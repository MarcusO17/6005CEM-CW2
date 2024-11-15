<tr>
    <td width="10%">
    </td>
    <td width="5%" style="text-align: center;">
        Date:
    </td>
    <td width="30%">
        <form action="" method="post">
            <input type="date" name="sheduledate" id="date" class="input-text filter-container-items" style="margin: 0;width: 95%;">
    </td>
    <td width="5%" style="text-align: center;">
        Doctor:
    </td>
    <td width="30%">
        <select name="docid" id="" class="box filter-container-items" style="width:90% ;height: 37px;margin: 0;">
            <option value="" disabled selected hidden>Choose Doctor Name from the list</option><br />
            <?php
            $list11 = $database->query("select  * from  doctor order by docname asc;");
            for ($y = 0; $y < $list11->num_rows; $y++) {
                $row00 = $list11->fetch_assoc();
                $sn = $row00["docname"];
                $id00 = $row00["docid"];
                echo "<option value=" . $id00 . ">$sn</option><br/>";
            }
            ?>
        </select>
    </td>
    <td width="12%">
        <input type="submit" name="filter" value=" Filter" class=" btn-primary-soft btn button-icon btn-filter" style="padding: 15px; margin :0;width:100%">
        </form>
    </td>
</tr> 