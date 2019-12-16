<form method="post" action="/output">
  <table class="table is-fullwidth is-hoverable is-striped">
    <thead>
      <tr>
        <th>Endpoint</th>
        <th>URL Pattern</th>
        <th>Host</th>
        <th>Method</th>
        <th>Proxy</th>
        <th></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th>Endpoint</th>
        <th>URL Pattern</th>
        <th>Host</th>
        <th>Method</th>
        <th>Proxy</th>
        <th></th>
      </tr>
    </tfoot>
    <?php foreach (($SESSION['OUTPUT']?:[]) as $o): ?>
      <tbody>
        <tr>
          <td><input class="input" type="text" value="<?= ($o['endpoint_fe']) ?>" name="endpoint_fe[]" required></td>
          <td><input class="input" type="text" value="<?= ($o['endpoint_be']) ?>" name="endpoint_be[]" required></td>
          <td><input class="input" type="text" value="<?= ($o['host']) ?>" name="host[]" required></td>
          <td>
            <div class="select">
              <select name="method[]">
                <option <?php if ($o['method'] == 'GET'): ?>selected<?php endif; ?> value="GET">GET</option>
                <option <?php if ($o['method'] == 'POST'): ?>selected<?php endif; ?> value="POST">POST</option>
                <option <?php if ($o['method'] == 'PUT'): ?>selected<?php endif; ?> value="PUT">PUT</option>
                <option <?php if ($o['method'] == 'DELETE'): ?>selected<?php endif; ?> value="DELETE">DELETE</option>
              </select>
            </div>
          </td>
          <td>
            <div class="select">
              <select name="encoding[]">
                <option value="no-op">No</option>
                <option <?php if ($o['encoding'] == 'json'): ?>selected<?php endif; ?> value="json">Yes</option>
              </select>
            </div>
          </td>
          <td><span class="delete hapus"></span></td>
        </tr>
      </tbody>
    <?php endforeach; ?>
  </table>
  <button class="button is-primary is-pulled-right is-medium">
    <span class="icon is-small">
      <i class="fas fa-save"></i>
    </span>
    <span>Simpan</span>
  </button>
</form>