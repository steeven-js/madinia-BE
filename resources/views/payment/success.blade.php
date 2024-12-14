@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Paiement réussi</div>
                <div class="card-body">
                    <div class="alert alert-success">
                        Votre paiement a été effectué avec succès !
                    </div>
                    <p>Numéro de transaction : {{ $session_id }}</p>
                    <a href="/" class="btn btn-primary">Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
