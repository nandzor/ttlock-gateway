<table>
  <thead>
    <tr>
      <th>Date</th>
      <th>Lock ID</th>
      <th>Lock MAC</th>
      <th>Event</th>
      <th>Record Type</th>
      <th>Battery</th>
      <th>User</th>
      <th>Processed</th>
    </tr>
  </thead>
  <tbody>
    @foreach(($histories ?? []) as $h)
      <tr>
        <td>{{ $h->created_at->format('Y-m-d H:i:s') }}</td>
        <td>{{ $h->lock_id }}</td>
        <td>{{ $h->lock_mac }}</td>
        <td>{{ $h->event_type_description }} - {{ $h->message }}</td>
        <td>{{ $h->record_type_description }}</td>
        <td>{{ $h->electric_quantity }}% ({{ $h->battery_level_description }})</td>
        <td>{{ $h->username ?? '-' }}</td>
        <td>{{ $h->processed ? 'Yes' : 'No' }}</td>
      </tr>
    @endforeach
  </tbody>
</table>


