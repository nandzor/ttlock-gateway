@extends('layouts.app')

@section('title', 'Import HPS Elektronik')
@section('page-title', 'Import HPS Elektronik')

@section('content')
  <x-import-form :action="route('hps-elektronik.import.store')" :templateRoute="route('hps-elektronik.import.template')" title="Import HPS Elektronik" />
@endsection


