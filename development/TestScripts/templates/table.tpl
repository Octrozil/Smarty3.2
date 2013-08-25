{function name=mytable}
<table>{foreach $data as $row}
    <tr>{foreach $row as $column}
            <td>{$column} </td>
        {/foreach}</tr>
{/foreach}
    <table>
{/function}