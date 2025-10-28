@extends('layouts.app')

@section('title', 'Import HPS Emas')
@section('page-title', 'Import HPS Emas')

@section('content')
  <x-import-form :action="route('hps-emas.import.store')" :templateRoute="route('hps-emas.import.template')" title="Import HPS Emas" />
@endsection


