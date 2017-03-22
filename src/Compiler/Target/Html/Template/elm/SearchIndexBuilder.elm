port module SearchIndexBuilder exposing (..)

import Platform
import ElmTextSearch exposing (..)

port input: (List SearchDocument -> msg) -> Sub msg
port output: String -> Cmd msg

main =
    Platform.program { init = ((), Cmd.none), subscriptions = \model -> input Export, update = update }

type alias Model = ()
type alias SearchDocuments = List SearchDocument

type alias SearchDocument =
    { id: String
    , name: String
    , normalizedName: String
    , description: String
    , url: String
    }

type Message =
    Export SearchDocuments
        
update: Message -> Model -> (Model, Cmd Message)
update message model =
    case message of
        Export database ->
            (model, output (exportIndex database))

index: ElmTextSearch.Index SearchDocument
index =
    ElmTextSearch.new
        { ref = .id
        , fields =
            [ (.normalizedName, 5.0)
            , (.description, 2.0)
            , (.url, 0.5)
            ]
        , listFields = []
        }

exportIndex: SearchDocuments -> String
exportIndex database =
    ElmTextSearch.storeToString (Tuple.first (ElmTextSearch.addDocs database index))
