port module SearchIndexBuilder exposing (..)

import Platform
import ElmTextSearch exposing (..)

port input: (List SearchIndexDocument -> msg) -> Sub msg
port output: String -> Cmd msg

main =
    Platform.program { init = ((), Cmd.none), subscriptions = \model -> input Export, update = update }

type alias Model = ()
type alias SearchIndexDocuments = List SearchIndexDocument

type alias SearchIndexDocument =
    { id: String
    , normalizedName: String
    , description: String
    }

type Message =
    Export SearchIndexDocuments
        
update: Message -> Model -> (Model, Cmd Message)
update message model =
    case message of
        Export database ->
            (model, output (exportIndex database))

index: ElmTextSearch.Index SearchIndexDocument
index =
    ElmTextSearch.new
        { ref = .id
        , fields =
            [ (.normalizedName, 3.0)
            , (.description, 1.0)
            ]
        , listFields = []
        }

exportIndex: SearchIndexDocuments -> String
exportIndex database =
    ElmTextSearch.storeToString (Tuple.first (ElmTextSearch.addDocs database index))
