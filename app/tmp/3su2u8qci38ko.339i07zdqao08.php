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
    <tbody>
      <tr>
        <td><input class="input" type="text" name="endpoint_fe[]" required></td>
        <td><input class="input" type="text" name="endpoint_be[]" required></td>
        <td><input class="input" type="text" name="host[]" required></td>
        <td>
          <div class="select">
            <select name="method[]">
              <option value="GET">GET</option>
              <option value="POST">POST</option>
              <option value="PUT">PUT</option>
              <option value="DELETE">DELETE</option>
            </select>
          </div>
        </td>
        <td>
          <div class="select">
            <select name="encoding[]">
              <option value="json">Yes</option>
              <option value="no-op">No</option>
            </select>
          </div>
        </td>
        <td>
          <span class="is-size-3 has-text-danger hapus"><i class="fas fa-minus-square"></i></span>
          <span class="is-size-3 has-text-success tambah"><i class="fas fa-plus-square"></i></span>
        </td>
      </tr>
    </tbody>
  </table>
  <a href="/" class="button is-warning is-medium" title="Upload &amp; Edit Endpoints">
    <span class="icon is-small">
      <i class="fas fa-long-arrow-alt-left"></i>
    </span>
  </a>
  <button class="button is-info is-pulled-right is-medium">
    <span class="icon is-small">
      <i class="fas fa-file-export"></i>
    </span>
    <span>Create</span>
  </button>
</form>